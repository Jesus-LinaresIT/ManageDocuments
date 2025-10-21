<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProjectDocument;
use App\Models\Review;
use App\Notifications\DocumentApprovedNotification;
use App\Notifications\DocumentDeniedNotification;
use App\Notifications\DocumentReadyForStage2Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = ProjectDocument::with(['project.teacher', 'documentType', 'documentVersions'])
            ->whereHas('project');

        // Filtrar por rol del revisor
        if ($user->hasRole('Coordinador de Proyección Social')) {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('rev_academic_id', $user->id);
            })->whereIn('status', ['sent', 'denied']);
        } elseif ($user->hasRole('Director de Proyección Social')) {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('rev_social_id', $user->id);
            })->where('status', 'approved_stage1');
        } else {
            // Admin puede ver todos
            $query->whereIn('status', ['sent', 'approved_stage1', 'denied']);
        }

        // Aplicar filtros
        if (request('project_id')) {
            $query->where('project_id', request('project_id'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('date_from')) {
            $query->whereDate('updated_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('updated_at', '<=', request('date_to'));
        }

        $documents = $query->orderBy('updated_at', 'desc')->paginate(10);
        
        return view('reviews.index', compact('documents'));
    }

    public function show(ProjectDocument $projectDocument)
    {
        $user = Auth::user();
        
        // Verificar permisos básicos de acceso
        if ($user->hasRole('Coordinador de Proyección Social')) {
            if ($projectDocument->project->rev_academic_id !== $user->id) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento.');
            }
            // Verificar si el documento está en un estado visible para Etapa 1
            if (!in_array($projectDocument->status, ['sent', 'denied'])) {
                return redirect()->route('reviews.index')->with('warning', 'Este documento ya no está disponible para su etapa de revisión académica.');
            }
        } elseif ($user->hasRole('Director de Proyección Social')) {
            if ($projectDocument->project->rev_social_id !== $user->id) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento.');
            }
            // Verificar si el documento está en un estado visible para Etapa 2
            if ($projectDocument->status !== 'approved_stage1') {
                return redirect()->route('reviews.index')->with('warning', 'Este documento ya no está disponible para su etapa de revisión de proyección social.');
            }
        } else {
            // Usuario sin permisos de revisión
            return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar documentos.');
        }

        // Registrar visualización
        $projectDocument->update(['viewed_at' => now()]);
        
        // Log de auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.viewed',
            'meta' => [
                'project_document_id' => $projectDocument->id,
                'project_id' => $projectDocument->project_id,
                'document_type' => $projectDocument->documentType->name,
            ]
        ]);

        $projectDocument->load(['project.teacher', 'documentType', 'documentVersions', 'reviews.reviewer']);
        
        return view('reviews.show', compact('projectDocument'));
    }

    public function approve(Request $request, ProjectDocument $projectDocument)
    {
        $user = Auth::user();
        $request->validate([
            'observation' => 'nullable|string|max:1000',
        ]);

        // Verificar permisos y estado
        if ($user->hasRole('Coordinador de Proyección Social')) {
            if ($projectDocument->project->rev_academic_id !== $user->id) {
                abort(403, 'No tienes permisos para revisar este documento.');
            }
            if (!in_array($projectDocument->status, ['sent', 'denied'])) {
                abort(403, 'Este documento no está disponible para revisión académica.');
            }
            $stage = 'stage1';
            $newStatus = 'approved_stage1';
        } elseif ($user->hasRole('Director de Proyección Social')) {
            if ($projectDocument->project->rev_social_id !== $user->id) {
                abort(403, 'No tienes permisos para revisar este documento.');
            }
            if ($projectDocument->status !== 'approved_stage1') {
                abort(403, 'Este documento no está disponible para revisión de proyección social.');
            }
            $stage = 'stage2';
            $newStatus = 'approved';
        } else {
            abort(403, 'No tienes permisos para revisar documentos.');
        }

        // Crear registro de revisión
        $review = Review::create([
            'project_document_id' => $projectDocument->id,
            'reviewer_id' => $user->id,
            'stage' => $stage,
            'decision' => 'approved',
            'observation' => $request->observation,
        ]);

        // Actualizar estado del documento
        $projectDocument->update([
            'status' => $newStatus,
            'last_observation' => $request->observation,
        ]);

        // Log de auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.approved',
            'meta' => [
                'project_document_id' => $projectDocument->id,
                'project_id' => $projectDocument->project_id,
                'stage' => $stage,
                'review_id' => $review->id,
            ]
        ]);

        // Enviar notificaciones
        if ($stage === 'stage1') {
            // Notificar al docente
            $projectDocument->project->teacher->notify(
                new DocumentApprovedNotification($projectDocument, $stage)
            );
            
            // Notificar al revisor social
            $projectDocument->project->revSocial->notify(
                new DocumentReadyForStage2Notification($projectDocument)
            );
        } else {
            // Notificar al docente y revisor académico
            $projectDocument->project->teacher->notify(
                new DocumentApprovedNotification($projectDocument, $stage)
            );
            $projectDocument->project->revAcademic->notify(
                new DocumentApprovedNotification($projectDocument, $stage)
            );
        }

        // Redirigir a la bandeja con mensaje específico según la etapa
        if ($stage === 'stage1') {
            return redirect()->route('reviews.index')->with('success', 'Documento aprobado en Etapa 1 (Revisión Académica).');
        } else {
            return redirect()->route('reviews.index')->with('success', 'Documento aprobado en Etapa 2 (Revisión de Proyección Social).');
        }
    }

    public function deny(Request $request, ProjectDocument $projectDocument)
    {
        $user = Auth::user();
        $request->validate([
            'observation' => 'required|string|max:1000',
        ], [
            'observation.required' => 'La observación es obligatoria al denegar un documento.',
        ]);

        // Verificar permisos y estado
        if ($user->hasRole('Coordinador de Proyección Social')) {
            if ($projectDocument->project->rev_academic_id !== $user->id) {
                abort(403, 'No tienes permisos para revisar este documento.');
            }
            if (!in_array($projectDocument->status, ['sent', 'denied'])) {
                abort(403, 'Este documento no está disponible para revisión académica.');
            }
            $stage = 'stage1';
        } elseif ($user->hasRole('Director de Proyección Social')) {
            if ($projectDocument->project->rev_social_id !== $user->id) {
                abort(403, 'No tienes permisos para revisar este documento.');
            }
            if ($projectDocument->status !== 'approved_stage1') {
                abort(403, 'Este documento no está disponible para revisión de proyección social.');
            }
            $stage = 'stage2';
        } else {
            abort(403, 'No tienes permisos para revisar documentos.');
        }

        // Crear registro de revisión
        $review = Review::create([
            'project_document_id' => $projectDocument->id,
            'reviewer_id' => $user->id,
            'stage' => $stage,
            'decision' => 'denied',
            'observation' => $request->observation,
        ]);

        // Actualizar estado del documento
        $projectDocument->update([
            'status' => 'denied',
            'last_observation' => $request->observation,
        ]);

        // Log de auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.denied',
            'meta' => [
                'project_document_id' => $projectDocument->id,
                'project_id' => $projectDocument->project_id,
                'stage' => $stage,
                'review_id' => $review->id,
                'observation' => $request->observation,
            ]
        ]);

        // Enviar notificación al docente
        $projectDocument->project->teacher->notify(
            new DocumentDeniedNotification($projectDocument, $stage, $request->observation)
        );

        // Redirigir a la bandeja con mensaje específico según la etapa
        if ($stage === 'stage1') {
            return redirect()->route('reviews.index')->with('success', 'Documento denegado en Etapa 1 (Revisión Académica). El docente ha sido notificado para realizar las correcciones necesarias.');
        } else {
            return redirect()->route('reviews.index')->with('success', 'Documento denegado en Etapa 2 (Revisión de Proyección Social). El docente ha sido notificado para realizar las correcciones necesarias.');
        }
    }
}
