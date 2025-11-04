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

                    @if(session('warning'))
                        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            {{ session('warning') }}
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
                                            'pending_stage2' => 'bg-yellow-100 text-yellow-800', // Para docente, mostrar como "En Revisión"
                                            'pending_stage3' => 'bg-yellow-100 text-yellow-800', // Para docente, mostrar como "En Revisión"
                                            'approved' => 'bg-green-100 text-green-800',
                                            'denied' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendiente',
                                            'sent' => 'Enviado',
                                            'pending_stage2' => 'En Revisión', // Para docente, mostrar como "En Revisión"
                                            'pending_stage3' => 'En Revisión', // Para docente, mostrar como "En Revisión"
                                            'approved' => 'Aprobado',
                                            'denied' => 'Denegado',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$projectDocument->status] }}">
                                        {{ $statusLabels[$projectDocument->status] }}
                                    </span>
                                </div>

                                @if($projectDocument->last_observation && !in_array($projectDocument->status, ['pending_stage2', 'pending_stage3']))
                                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Observación:</strong> {{ $projectDocument->last_observation }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Verificar bloqueo y desbloqueo de documentos -->
                                @php
                                    $isBlocked = false;
                                    $blockReason = '';
                                    $showBlockedMessage = false;

                                    // NUEVA REGLA: Solo bloquear si es Documento 1 (sequence = 1) y está aprobado
                                    if ($projectDocument->documentType->sequence === 1 && $projectDocument->status === 'approved') {
                                        $isBlocked = true;
                                        $showBlockedMessage = true;
                                        $blockReason = 'El Documento 1 ya fue aprobado definitivamente y no admite nuevas cargas.';
                                    }
                                    // Verificar si el Documento 1 está aprobado para desbloquear documentos 2-5
                                    elseif ($projectDocument->documentType->sequence > 1) {
                                        $documentType1 = \App\Models\DocumentType::where('sequence', 1)->first();
                                        $projectDocument1 = \App\Models\ProjectDocument::where('project_id', $project->id)
                                            ->where('document_type_id', $documentType1->id)
                                            ->first();

                                        if (!$projectDocument1 || $projectDocument1->status !== 'approved') {
                                            $isBlocked = true;
                                            $showBlockedMessage = true;
                                            $blockReason = "El Documento 1 ({$documentType1->name}) debe ser aprobado antes de poder subir otros documentos.";
                                        }
                                    }
                                @endphp

                                @if($showBlockedMessage)
                                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-sm text-yellow-800">
                                            <strong>Bloqueado:</strong> {{ $blockReason }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Formulario de carga -->
                                @if($projectDocument->documentType->sequence === 1 && $projectDocument->status === 'approved')
                                    <!-- Para Doc1 aprobado: mostrar badge y ocultar formulario -->
                                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">
                                        <p class="text-sm text-green-800">
                                            <strong>Aprobado (bloqueado):</strong> Este documento ya fue aprobado definitivamente y no admite nuevas cargas.
                                        </p>
                                    </div>
                                @else
                                    <!-- Para todos los demás casos: mostrar formulario -->
                                    <form action="{{ route('projects.docs.upload', [$project, $projectDocument->documentType]) }}"
                                          method="POST" 
                                          enctype="multipart/form-data" 
                                          class="mb-4">
                                        @csrf
                                        <div class="flex items-center space-x-4">
                                            <input type="file" 
                                                   name="file" 
                                                   accept=".pdf,.docx"
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                   {{ $isBlocked ? 'disabled' : '' }}
                                                   required>
                                            <button type="submit"
                                                    class="js-upload-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded {{ $isBlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    {{ $isBlocked ? 'disabled' : '' }}
                                                    onclick="const btn=this; if(btn.classList.contains('cursor-not-allowed')) return; btn.dataset.orig=btn.innerHTML; btn.innerHTML='<span class=\'inline-flex items-center\'><svg class=\'animate-spin -ml-1 mr-2 h-4 w-4 text-white\' xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg>Cargando...</span>'; btn.classList.add('opacity-50','cursor-not-allowed');">
                                                {{ $projectDocument->documentVersions->count() > 0 ? 'Subir Nueva Versión' : 'Subir' }}
                                            </button>
                                        </div>
                                    </form>
                                @endif

                                <!-- Versión del documento (solo última) -->
                                @if($projectDocument->documentVersions->count() > 0)
                                    @php $version = $projectDocument->documentVersions->sortByDesc('version')->first(); @endphp
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Version del Documento</h5>
                                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                            <div>
                                                <span class="text-sm font-medium">Versión {{ $version->version }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $version->original_name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ number_format($version->size / 1024, 2) }} KB</span>
                                            </div>
                                            <div class="flex space-x-2">
                                                <span class="text-xs text-gray-500">{{ $version->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                                                <a href="{{ route('documents.download', $version) }}"
                                                   class="text-blue-600 hover:text-blue-800 text-xs">Descargar</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex space-x-2">
                        <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver al Dashboard
                        </a>
                        <a href="{{ route('projects.show', $project) }}" class="bg-gray-400 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                            Volver al Proyecto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


