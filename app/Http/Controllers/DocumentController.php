<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Project $project)
    {
        $project->load(['projectDocuments.documentType', 'projectDocuments.documentVersions']);
        
        return view('projects.docs', compact('project'));
    }

    public function upload(Request $request, Project $project, DocumentType $documentType)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx|max:20480', // 20MB max
        ]);

        // Verificar que el usuario es el docente del proyecto
        if (Auth::id() !== $project->teacher_id) {
            return back()->with('error', 'No tienes permisos para subir documentos a este proyecto.');
        }

        // Verificar bloqueo secuencial
        $projectDocument = ProjectDocument::where('project_id', $project->id)
            ->where('document_type_id', $documentType->id)
            ->first();

        if (!$projectDocument) {
            return back()->with('error', 'Documento no encontrado.');
        }

        // Verificar si el documento anterior está aprobado
        if ($documentType->sequence > 1) {
            $previousDocumentType = DocumentType::where('sequence', $documentType->sequence - 1)->first();
            $previousProjectDocument = ProjectDocument::where('project_id', $project->id)
                ->where('document_type_id', $previousDocumentType->id)
                ->first();

            if (!$previousProjectDocument || $previousProjectDocument->status !== 'approved') {
                return back()->with('error', "Debes completar y aprobar el documento anterior ({$previousDocumentType->name}) antes de subir este documento.");
            }
        }

        // Validar tipo MIME y tamaño
        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        if (!in_array($mimeType, $documentType->allowed_mime)) {
            return back()->with('error', 'Tipo de archivo no permitido.');
        }

        if ($fileSize > ($documentType->max_mb * 1024 * 1024)) {
            return back()->with('error', 'El archivo excede el tamaño máximo permitido.');
        }

        // Crear directorio si no existe
        $directory = "projects/{$project->id}/doc{$documentType->sequence}";
        Storage::disk('public')->makeDirectory($directory);

        // Generar nombre único para el archivo
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $filePath = $file->storeAs($directory, $fileName, 'public');

        // Obtener la siguiente versión
        $nextVersion = DocumentVersion::where('project_document_id', $projectDocument->id)
            ->max('version') + 1;

        // Crear nueva versión del documento
        DocumentVersion::create([
            'project_document_id' => $projectDocument->id,
            'original_name' => $originalName,
            'path' => $filePath,
            'mime' => $mimeType,
            'size' => $fileSize,
            'version' => $nextVersion,
        ]);

        // Actualizar estado del documento
        $projectDocument->update([
            'status' => 'sent',
            'viewed_at' => null,
        ]);

        // Log de auditoría
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'document.upload',
            'meta' => [
                'project_id' => $project->id,
                'document_type_id' => $documentType->id,
                'version' => $nextVersion,
                'file_name' => $originalName,
            ]
        ]);

        return back()->with('success', 'Documento subido exitosamente.');
    }

    public function download(DocumentVersion $documentVersion)
    {
        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($documentVersion->path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Verificar permisos - solo el docente del proyecto o revisores asignados pueden descargar
        $documentVersion->load('projectDocument.project');
        $project = $documentVersion->projectDocument->project;
        $user = Auth::user();
        
        $canDownload = false;
        
        // El docente del proyecto puede descargar
        if ($user->id === $project->teacher_id) {
            $canDownload = true;
        }
        
        // Los revisores asignados pueden descargar
        if ($user->id === $project->rev_academic_id || $user->id === $project->rev_social_id) {
            $canDownload = true;
        }
        
        // Los administradores pueden descargar
        if ($user->hasRole('Administrador')) {
            $canDownload = true;
        }
        
        if (!$canDownload) {
            abort(403, 'No tienes permisos para descargar este archivo.');
        }

        // Log de auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'document.downloaded',
            'meta' => [
                'project_document_id' => $documentVersion->project_document_id,
                'project_id' => $project->id,
                'version' => $documentVersion->version,
                'file_name' => $documentVersion->original_name,
            ]
        ]);

        // Descargar con nombre original
        return Storage::disk('public')->download($documentVersion->path, $documentVersion->original_name);
    }
}
