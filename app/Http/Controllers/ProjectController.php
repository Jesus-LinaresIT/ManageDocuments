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
        $query = Project::with(['teacher', 'revAcademic', 'revSocial'])->latest();

        if ($user->hasRole('Administrador') || $user->hasRole('Director de Proyección Social')) {
            // Admin y Director ven todos los proyectos
            $projects = $query->paginate(15);
        } elseif ($user->hasRole('Coordinador de Proyección Social') || $user->hasRole('Decano/a')) {
            // Coordinador y Decano ven proyectos de su unidad O proyectos que ellos crearon
            $unit = $user->unit ?? null;
            if ($unit) {
                $query->where(function($q) use ($unit, $user) {
                    $q->where('unit', $unit)
                      ->orWhere('rev_academic_id', $user->id);
                });
            } else {
                $query->where('rev_academic_id', $user->id);
            }
            $projects = $query->paginate(15);
        } else {
            // Docente: solo sus proyectos
            $projects = $query->where('teacher_id', $user->id)->paginate(15);
        }

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $teachers = User::role('Docente')->get();
        $academicReviewers = User::role('Coordinador de Proyección Social')->get();
        $socialReviewers = User::role('Director de Proyección Social')->get();
        
        $user = Auth::user();
        $preloadedData = null;
        
        // Si es Coordinador, precargar datos
        if ($user->hasRole('Coordinador de Proyección Social')) {
            $director = User::role('Director de Proyección Social')->first();
            $preloadedData = [
                'rev_academic_id' => $user->id, // El coordinador es el revisor académico
                'rev_academic_name' => $user->name, // Nombre del coordinador
                'rev_social_id' => $director?->id ?? null,
                'rev_social_name' => $director?->name ?? 'Lic. William Antonio Geliz'
            ];
        }

        return view('projects.create', compact('teachers', 'academicReviewers', 'socialReviewers', 'preloadedData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'period' => 'required|string|in:Ciclo1,Ciclo2',
            'unit' => 'required|string|in:FICA,FACE,FADE,FACS',
            'target_date' => 'required|date',
            'teacher_id' => 'required|exists:users,id',
            'rev_academic_id' => 'required|exists:users,id',
            'rev_social_id' => 'required|exists:users,id',
        ]);

        // Obtener el año actual y concatenarlo al período
        $currentYear = date('Y');
        $periodWithYear = $request->period . '-' . $currentYear;

        // Preparar los datos para crear el proyecto con el período concatenado
        $projectData = $request->all();
        $projectData['period'] = $periodWithYear;

        $project = Project::create($projectData);

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
        $user = Auth::user();
        
        // Autorizar acceso según rol
        if ($user->hasRole('Administrador')) {
            // Admin puede ver todos
        } elseif ($user->hasRole('Coordinador de Proyección Social') || $user->hasRole('Decano/a')) {
            // Coordinador y Decano pueden ver proyectos de su unidad O proyectos que ellos crearon
            $unit = $user->unit ?? null;
            $canView = false;
            
            // Pueden ver si es de su unidad
            if ($unit && $project->unit === $unit) {
                $canView = true;
            }
            
            // Pueden ver si ellos crearon el proyecto (como coordinador académico)
            if ($project->rev_academic_id === $user->id) {
                $canView = true;
            }
            
            if (!$canView) {
                abort(403, 'No tienes permisos para ver este proyecto.');
            }
        } elseif ($user->hasRole('Director de Proyección Social')) {
            // Director puede ver todos
        } elseif ($user->hasRole('Docente')) {
            // Docente solo puede ver sus propios proyectos
            if ($project->teacher_id !== $user->id) {
                abort(403, 'No tienes permisos para ver este proyecto.');
            }
        } else {
            abort(403, 'No tienes permisos para ver proyectos.');
        }
        
        $project->load(['teacher', 'revAcademic', 'revSocial', 'projectDocuments.documentType']);
        
        return view('projects.show', compact('project'));
    }
}
