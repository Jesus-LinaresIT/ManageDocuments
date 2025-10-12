<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Review;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('view.reports');

        // Métricas generales
        $totalProjects = Project::count();
        $totalDocuments = ProjectDocument::count();
        $approvedDocuments = ProjectDocument::where('status', 'approved')->count();
        $pendingDocuments = ProjectDocument::whereIn('status', ['sent', 'approved_stage1'])->count();
        $deniedDocuments = ProjectDocument::where('status', 'denied')->count();

        // Porcentaje de avance por proyecto
        $projectProgress = Project::with(['projectDocuments' => function($query) {
            $query->where('status', 'approved');
        }])->get()->map(function($project) {
            $approvedCount = $project->projectDocuments->count();
            $totalDocuments = 5; // Total esperado de documentos por proyecto
            $progress = $totalDocuments > 0 ? ($approvedCount / $totalDocuments) * 100 : 0;
            
            return [
                'project' => $project,
                'approved_count' => $approvedCount,
                'total_expected' => $totalDocuments,
                'progress_percentage' => round($progress, 2)
            ];
        });

        // Tiempo promedio de revisión
        $averageReviewTime = Review::selectRaw('
            AVG(TIMESTAMPDIFF(HOUR, project_documents.updated_at, reviews.created_at)) as avg_hours
        ')
        ->join('project_documents', 'reviews.project_document_id', '=', 'project_documents.id')
        ->where('reviews.decision', 'approved')
        ->first();

        // Tasa de denegación por etapa
        $denialRates = Review::selectRaw('
            stage,
            COUNT(*) as total_reviews,
            SUM(CASE WHEN decision = "denied" THEN 1 ELSE 0 END) as denied_count,
            ROUND((SUM(CASE WHEN decision = "denied" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as denial_rate
        ')
        ->groupBy('stage')
        ->get();

        // Actividad reciente
        $recentActivity = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Documentos por estado
        $documentsByStatus = ProjectDocument::selectRaw('
            status,
            COUNT(*) as count
        ')
        ->groupBy('status')
        ->get();

        // Proyectos por período
        $projectsByPeriod = Project::selectRaw('
            period,
            COUNT(*) as count
        ')
        ->groupBy('period')
        ->orderBy('period', 'desc')
        ->get();

        return view('reports.index', compact(
            'totalProjects',
            'totalDocuments',
            'approvedDocuments',
            'pendingDocuments',
            'deniedDocuments',
            'projectProgress',
            'averageReviewTime',
            'denialRates',
            'recentActivity',
            'documentsByStatus',
            'projectsByPeriod'
        ));
    }

    public function projectDetails(Project $project)
    {
        $this->authorize('view.reports');

        $project->load(['projectDocuments.documentType', 'projectDocuments.documentVersions', 'projectDocuments.reviews.reviewer']);

        // Calcular métricas específicas del proyecto
        $approvedCount = $project->projectDocuments->where('status', 'approved')->count();
        $totalExpected = 5;
        $progressPercentage = $totalExpected > 0 ? ($approvedCount / $totalExpected) * 100 : 0;

        // Tiempo promedio de revisión para este proyecto
        $projectReviewTime = Review::selectRaw('
            AVG(TIMESTAMPDIFF(HOUR, project_documents.updated_at, reviews.created_at)) as avg_hours
        ')
        ->join('project_documents', 'reviews.project_document_id', '=', 'project_documents.id')
        ->where('project_documents.project_id', $project->id)
        ->where('reviews.decision', 'approved')
        ->first();

        // Historial de actividad del proyecto
        $projectActivity = AuditLog::where('meta->project_id', $project->id)
            ->orWhere('meta->project_document_id', function($query) use ($project) {
                $query->select('id')
                    ->from('project_documents')
                    ->where('project_id', $project->id);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.project-details', compact(
            'project',
            'approvedCount',
            'totalExpected',
            'progressPercentage',
            'projectReviewTime',
            'projectActivity'
        ));
    }
}
