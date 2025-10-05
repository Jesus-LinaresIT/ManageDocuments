<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Documentos del Proyecto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">{{ $project->name }}</h3>
                        <p class="text-sm text-gray-500">Período: {{ $project->period }}</p>
                    </div>

                    <div class="space-y-6">
                        @foreach($project->projectDocuments as $projectDocument)
                            <div class="border border-gray-200 rounded-lg p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-lg font-medium">{{ $projectDocument->documentType->name }}</h4>
                                        <p class="text-sm text-gray-500">
                                            Secuencia: {{ $projectDocument->documentType->sequence }} | 
                                            Tipos permitidos: PDF, DOCX | 
                                            Tamaño máximo: {{ $projectDocument->documentType->max_mb }}MB
                                        </p>
                                    </div>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'sent' => 'bg-yellow-100 text-yellow-800',
                                            'approved_stage1' => 'bg-blue-100 text-blue-800',
                                            'in_stage2' => 'bg-purple-100 text-purple-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'denied' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendiente',
                                            'sent' => 'Enviado',
                                            'approved_stage1' => 'Aprobado Etapa 1',
                                            'in_stage2' => 'En Etapa 2',
                                            'approved' => 'Aprobado',
                                            'denied' => 'Denegado',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$projectDocument->status] }}">
                                        {{ $statusLabels[$projectDocument->status] }}
                                    </span>
                                </div>

                                @if($projectDocument->last_observation)
                                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Observación:</strong> {{ $projectDocument->last_observation }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Verificar bloqueo secuencial -->
                                @php
                                    $isBlocked = false;
                                    $blockReason = '';
                                    if ($projectDocument->documentType->sequence > 1) {
                                        $previousDocumentType = \App\Models\DocumentType::where('sequence', $projectDocument->documentType->sequence - 1)->first();
                                        $previousProjectDocument = \App\Models\ProjectDocument::where('project_id', $project->id)
                                            ->where('document_type_id', $previousDocumentType->id)
                                            ->first();
                                        
                                        if (!$previousProjectDocument || $previousProjectDocument->status !== 'approved') {
                                            $isBlocked = true;
                                            $blockReason = "Debes completar y aprobar el documento anterior ({$previousDocumentType->name}) antes de subir este documento.";
                                        }
                                    }
                                @endphp

                                @if($isBlocked)
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
                                        <p class="text-sm text-red-800">
                                            <strong>Bloqueado:</strong> {{ $blockReason }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Formulario de carga -->
                                <form action="{{ route('projects.docs.upload', [$project, $projectDocument->documentType]) }}" 
                                      method="POST" enctype="multipart/form-data" class="mb-4">
                                    @csrf
                                    <div class="flex items-center space-x-4">
                                        <input type="file" name="file" accept=".pdf,.docx" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                               {{ $isBlocked ? 'disabled' : '' }}>
                                        <button type="submit" 
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded {{ $isBlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ $isBlocked ? 'disabled' : '' }}>
                                            {{ $projectDocument->documentVersions->count() > 0 ? 'Reintentar' : 'Subir' }}
                                        </button>
                                    </div>
                                </form>

                                <!-- Historial de versiones -->
                                @if($projectDocument->documentVersions->count() > 0)
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Historial de Versiones:</h5>
                                        <div class="space-y-2">
                                            @foreach($projectDocument->documentVersions->sortByDesc('version') as $version)
                                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                    <div>
                                                        <span class="text-sm font-medium">Versión {{ $version->version }}</span>
                                                        <span class="text-xs text-gray-500 ml-2">{{ $version->original_name }}</span>
                                                        <span class="text-xs text-gray-500 ml-2">{{ number_format($version->size / 1024, 2) }} KB</span>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <span class="text-xs text-gray-500">{{ $version->created_at->format('d/m/Y H:i') }}</span>
                                                        <a href="{{ Storage::url($version->path) }}" 
                                                           class="text-blue-600 hover:text-blue-800 text-xs" 
                                                           target="_blank">Descargar</a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver al Proyecto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


