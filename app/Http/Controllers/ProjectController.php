<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('Administrador')) {
            $projects = Project::with(['teacher', 'revAcademic', 'revSocial'])->get();
        } else {
            $projects = Project::where('teacher_id', $user->id)
                ->orWhere('rev_academic_id', $user->id)
                ->orWhere('rev_social_id', $user->id)
                ->with(['teacher', 'revAcademic', 'revSocial'])
                ->get();
        }

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $teachers = User::role('Docente')->get();
        $academicReviewers = User::role('Revisor Académico')->get();
        $socialReviewers = User::role('Revisor Proyección Social')->get();

        return view('projects.create', compact('teachers', 'academicReviewers', 'socialReviewers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'period' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'target_date' => 'required|date',
            'teacher_id' => 'required|exists:users,id',
            'rev_academic_id' => 'required|exists:users,id',
            'rev_social_id' => 'required|exists:users,id',
        ]);

        $project = Project::create($request->all());

        // Crear los 5 ProjectDocument para el proyecto
        $documentTypes = \App\Models\DocumentType::all();
        foreach ($documentTypes as $documentType) {
            ProjectDocument::create([
                'project_id' => $project->id,
                'document_type_id' => $documentType->id,
                'status' => 'pending',
            ]);
        }

        // Log de auditoría
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'project.create',
            'meta' => [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Proyecto creado exitosamente.');
    }

    public function show(Project $project)
    {
        $project->load(['teacher', 'revAcademic', 'revSocial', 'projectDocuments.documentType']);
        
        return view('projects.show', compact('project'));
    }
}
