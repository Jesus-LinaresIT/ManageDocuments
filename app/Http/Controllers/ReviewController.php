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

        // Filtrar por rol del revisor y etapa (sin filtros por creador)
        if ($user->hasRole('Coordinador de Proyección Social')) {
            // Coordinador ve documentos de Etapa 1 y Etapa 3
            // Puede ver documentos de su unidad O documentos donde es revisor académico
            if ($user->unit) {
                $query->where(function($q) use ($user) {
                    $q->whereHas('project', function($subQ) use ($user) {
                        $subQ->where('unit', $user->unit);
                    })->orWhereHas('project', function($subQ) use ($user) {
                        $subQ->where('rev_academic_id', $user->id);
                    });
                })->whereIn('status', ['sent', 'pending_stage3']);
            } else {
                // Si no tiene unidad definida, solo ver documentos donde es revisor académico
                $query->whereHas('project', function($q) use ($user) {
                    $q->where('rev_academic_id', $user->id);
                })->whereIn('status', ['sent', 'pending_stage3']);
            }
        } elseif ($user->hasRole('Director de Proyección Social')) {
            // Director ve documentos de Etapa 2, opcionalmente filtrados por unidad
            $query->whereIn('status', ['pending_stage2']);

        } elseif ($user->hasRole('Administrador')) {
            // Admin puede ver todos
            $query->whereIn('status', ['sent', 'pending_stage2', 'pending_stage3', 'approved', 'denied']);
        } else {
            // Otros roles no pueden acceder
            $query->where('id', 0); // Query vacío
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

        // Verificar permisos básicos de acceso (sin filtros por creador)
        if ($user->hasRole('Coordinador de Proyección Social')) {
            // Coordinador puede ver documentos de Etapa 1 y Etapa 3
            if (!in_array($projectDocument->status, ['sent', 'pending_stage3', 'denied'])) {
                return redirect()->route('reviews.index')->with('warning', 'Este documento ya no está disponible para su etapa de revisión académica.');
            }
            // Verificar permisos: puede ver si es de su unidad O si es revisor académico
            $canView = false;
            if ($user->unit && $projectDocument->project->unit === $user->unit) {
                $canView = true;
            }
            if ($projectDocument->project->rev_academic_id === $user->id) {
                $canView = true;
            }
            if (!$canView) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento.');
            }
        } elseif ($user->hasRole('Director de Proyección Social')) {
            // Director puede ver documentos de Etapa 2, opcionalmente filtrados por unidad
            if ($projectDocument->status !== 'pending_stage2') {
                return redirect()->route('reviews.index')->with('warning', 'Este documento ya no está disponible para su etapa de revisión de proyección social.');
            }
            // Verificar unidad si está definida
            if ($user->unit && $projectDocument->project->unit !== $user->unit) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento de otra unidad.');
            }
        } elseif ($user->hasRole('Administrador')) {
            // Admin puede ver todos los documentos
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

        // Verificar permisos y estado según el nuevo flujo de 3 etapas
        if ($user->hasRole('Coordinador de Proyección Social')) {
            // Coordinador puede aprobar en Etapa 1 y Etapa 3
            if ($projectDocument->status === 'sent') {
                // Etapa 1: Coordinador aprueba, pasa a Director
                $stage = 'stage1';
                $newStatus = 'pending_stage2';
            } elseif ($projectDocument->status === 'pending_stage3') {
                // Etapa 3: Coordinador da aprobación final
                $stage = 'stage3';
                $newStatus = 'approved';
            } else {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión académica.');
            }
            
            // Verificar permisos: puede aprobar si es de su unidad O si es revisor académico
            $canApprove = false;
            if ($user->unit && $projectDocument->project->unit === $user->unit) {
                $canApprove = true;
            }
            if ($projectDocument->project->rev_academic_id === $user->id) {
                $canApprove = true;
            }
            if (!$canApprove) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento.');
            }
            
        } elseif ($user->hasRole('Director de Proyección Social')) {
            // Director solo puede aprobar en Etapa 2
            if ($projectDocument->status !== 'pending_stage2') {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión de proyección social.');
            }
            
            // Verificar unidad si está definida
            if ($user->unit && $projectDocument->project->unit !== $user->unit) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento de otra unidad.');
            }
            
            $stage = 'stage2';
            $newStatus = 'pending_stage3'; // Director aprueba, regresa al Coordinador
            
        } elseif ($user->hasRole('Administrador')) {
            // Admin puede aprobar cualquier documento según el estado
            if ($projectDocument->status === 'sent') {
                $stage = 'stage1';
                $newStatus = 'pending_stage2';
            } elseif ($projectDocument->status === 'pending_stage2') {
                $stage = 'stage2';
                $newStatus = 'pending_stage3';
            } elseif ($projectDocument->status === 'pending_stage3') {
                $stage = 'stage3';
                $newStatus = 'approved';
            } else {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión.');
            }
        } else {
            return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar documentos.');
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

        // Enviar notificaciones según la etapa
        if ($stage === 'stage1') {
            // Etapa 1: Notificar al Director
            $projectDocument->project->revSocial->notify(
                new DocumentReadyForStage2Notification($projectDocument)
            );
        } elseif ($stage === 'stage2') {
            // Etapa 2: Notificar al Coordinador (no al docente aún)
            $projectDocument->project->revAcademic->notify(
                new DocumentReadyForStage2Notification($projectDocument)
            );
        } elseif ($stage === 'stage3') {
            // Etapa 3: Notificar al docente (aprobación final)
            $projectDocument->project->teacher->notify(
                new DocumentApprovedNotification($projectDocument, $stage)
            );
        }

        // Redirigir a la bandeja con mensaje específico según la etapa
        if ($stage === 'stage1') {
            return redirect()->route('reviews.index')->with('success', 'Documento aprobado en Etapa 1. Enviado al Director de Proyección Social.');
        } elseif ($stage === 'stage2') {
            return redirect()->route('reviews.index')->with('success', 'Documento aprobado en Etapa 2. Regresado al Coordinador para confirmación final.');
        } else {
            return redirect()->route('reviews.index')->with('success', 'Documento aprobado definitivamente. El docente ha sido notificado.');
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

        // Verificar permisos y estado según el nuevo flujo de 3 etapas
        if ($user->hasRole('Coordinador de Proyección Social')) {
            // Coordinador puede denegar en Etapa 1 y Etapa 3
            if (!in_array($projectDocument->status, ['sent', 'pending_stage3', 'denied'])) {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión académica.');
            }
            // Verificar permisos: puede denegar si es de su unidad O si es revisor académico
            $canDeny = false;
            if ($user->unit && $projectDocument->project->unit === $user->unit) {
                $canDeny = true;
            }
            if ($projectDocument->project->rev_academic_id === $user->id) {
                $canDeny = true;
            }
            if (!$canDeny) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento.');
            }
            $stage = $projectDocument->status === 'pending_stage3' ? 'stage3' : 'stage1';
        } elseif ($user->hasRole('Director de Proyección Social')) {
            // Director puede denegar en Etapa 2
            if ($projectDocument->status !== 'pending_stage2') {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión de proyección social.');
            }
            // Verificar unidad si está definida
            if ($user->unit && $projectDocument->project->unit !== $user->unit) {
                return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar este documento de otra unidad.');
            }
            $stage = 'stage2';
        } elseif ($user->hasRole('Administrador')) {
            // Admin puede denegar cualquier documento según el estado
            if (in_array($projectDocument->status, ['sent', 'denied'])) {
                $stage = 'stage1';
            } elseif ($projectDocument->status === 'pending_stage2') {
                $stage = 'stage2';
            } elseif ($projectDocument->status === 'pending_stage3') {
                $stage = 'stage3';
            } else {
                return redirect()->route('reviews.index')->with('warning', 'Este documento no está disponible para revisión.');
            }
        } else {
            return redirect()->route('reviews.index')->with('warning', 'No tienes permisos para revisar documentos.');
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
            return redirect()->route('reviews.index')->with('warning', 'Documento denegado en Etapa 1 (Revisión Académica). El docente ha sido notificado para realizar las correcciones necesarias.');
        } else {
            return redirect()->route('reviews.index')->with('warning', 'Documento denegado en Etapa 2 (Revisión de Proyección Social). El docente ha sido notificado para realizar las correcciones necesarias.');
        }
    }
}

