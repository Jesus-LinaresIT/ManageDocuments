<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\DocumentType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage.users');
    }

    public function index()
    {
        $documentTypes = DocumentType::orderBy('sequence')->get();
        return view('admin.document-types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('admin.document-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sequence' => 'required|integer|min:1|max:10|unique:document_types,sequence',
            'allowed_mime' => 'required|array|min:1',
            'allowed_mime.*' => 'string',
            'max_mb' => 'required|integer|min:1|max:100',
        ]);

        $documentType = DocumentType::create($request->all());

        // Log de auditoría
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'document_type.created',
            'meta' => [
                'document_type_id' => $documentType->id,
                'name' => $documentType->name,
                'sequence' => $documentType->sequence,
            ]
        ]);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Tipo de documento creado exitosamente.');
    }

    public function show(DocumentType $documentType)
    {
        $documentType->load('projectDocuments.project');
        return view('admin.document-types.show', compact('documentType'));
    }

    public function edit(DocumentType $documentType)
    {
        return view('admin.document-types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sequence' => 'required|integer|min:1|max:10|unique:document_types,sequence,' . $documentType->id,
            'allowed_mime' => 'required|array|min:1',
            'allowed_mime.*' => 'string',
            'max_mb' => 'required|integer|min:1|max:100',
        ]);

        $documentType->update($request->all());

        // Log de auditoría
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'document_type.updated',
            'meta' => [
                'document_type_id' => $documentType->id,
                'name' => $documentType->name,
                'sequence' => $documentType->sequence,
            ]
        ]);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Tipo de documento actualizado exitosamente.');
    }

    public function destroy(DocumentType $documentType)
    {
        // Verificar si hay documentos asociados
        if ($documentType->projectDocuments()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un tipo de documento que tiene documentos asociados.');
        }

        // Log de auditoría
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'document_type.deleted',
            'meta' => [
                'document_type_id' => $documentType->id,
                'name' => $documentType->name,
                'sequence' => $documentType->sequence,
            ]
        ]);

        $documentType->delete();

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Tipo de documento eliminado exitosamente.');
    }
}
