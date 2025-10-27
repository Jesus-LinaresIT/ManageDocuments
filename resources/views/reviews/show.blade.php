<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revisión del Documento') }}
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
                        <div class="rounded-md border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">{{ $projectDocument->documentType->name }}</h3>
                        <p class="text-sm text-gray-500">Proyecto: {{ $projectDocument->project->name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Información del Proyecto</h4>
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>Docente:</strong> {{ $projectDocument->project->teacher->name }}<br>
                                <strong>Período:</strong> {{ $projectDocument->project->period }}<br>
                                <strong>Unidad:</strong> {{ $projectDocument->project->unit }}<br>
                                <strong>Fecha objetivo:</strong> {{ $projectDocument->project->target_date->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Estado del Documento</h4>
                            <p class="text-sm text-gray-600 mt-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'sent' => 'bg-yellow-100 text-yellow-800',
                                        'pending_stage2' => 'bg-blue-100 text-blue-800',
                                        'pending_stage3' => 'bg-purple-100 text-purple-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'denied' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'sent' => 'Enviado',
                                        'pending_stage2' => 'Pendiente Etapa 2',
                                        'pending_stage3' => 'Pendiente Etapa 3',
                                        'approved' => 'Aprobado',
                                        'denied' => 'Denegado',
                                    ];
                                @endphp
                                <strong>Estado:</strong>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$projectDocument->status] }}">
                                    {{ $statusLabels[$projectDocument->status] }}
                                </span><br>
                                <strong>Secuencia:</strong> {{ $projectDocument->documentType->sequence }}<br>
                                <strong>Última actualización:</strong> {{ $projectDocument->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($projectDocument->last_observation)
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-medium text-yellow-900 mb-2">Observación Anterior</h4>
                            <p class="text-sm text-yellow-800">{{ $projectDocument->last_observation }}</p>
                        </div>
                    @endif

                    <!-- Historial de versiones -->
                    @if($projectDocument->documentVersions->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-lg font-medium mb-4">Versiones del Documento</h4>
                            <div class="space-y-3">
                                @foreach($projectDocument->documentVersions->sortByDesc('version') as $version)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded border">
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
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Historial de revisiones -->
                    @if($projectDocument->reviews->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-lg font-medium mb-4">Historial de Revisiones</h4>
                            <div class="space-y-3">
                                @php
                                    // Filtrar revisiones según el contexto
                                    $reviewsToShow = $projectDocument->reviews->sortByDesc('created_at');
                                    
                                    // Si el documento está en pending_stage3 y el usuario es coordinador,
                                    // solo mostrar la acción del Director (stage2)
                                    if ($projectDocument->status === 'pending_stage3' && auth()->user()->hasRole('Coordinador de Proyección Social')) {
                                        $reviewsToShow = $reviewsToShow->where('stage', 'stage2');
                                    }
                                @endphp
                                
                                @foreach($reviewsToShow as $review)
                                    <div class="p-3 bg-gray-50 rounded border">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <span class="text-sm font-medium">{{ $review->reviewer->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">
                                                    @if($review->stage === 'stage1')
                                                        Etapa 1
                                                    @elseif($review->stage === 'stage2')
                                                        Etapa 2
                                                    @elseif($review->stage === 'stage3')
                                                        Etapa 3
                                                    @endif
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $review->decision === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $review->decision === 'approved' ? 'Aprobado' : 'Denegado' }}
                                                </span>
                                            </div>
                                                <span class="text-xs text-gray-500">{{ $review->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if($review->observation)
                                            <p class="text-sm text-gray-600 mt-2">{{ $review->observation }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Formularios de revisión -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-blue-900 mb-4">Decisión de Revisión</h4>

                        @if($projectDocument->status === 'pending_stage3' && auth()->user()->hasRole('Coordinador de Proyección Social'))
                            <!-- Mostrar opciones de confirmación en Etapa 3 para Coordinador -->
                            @php
                                // Obtener la última revisión del Director (stage2)
                                $directorReview = $projectDocument->reviews->where('stage', 'stage2')->last();
                                $directorDecision = $directorReview ? $directorReview->decision : null;
                            @endphp
                            
                            @if($directorDecision === 'approved')
                                <!-- Director aprobó, mostrar confirmación de aprobación -->
                                <div class="max-w-md mx-auto">
                                    <form action="{{ route('reviews.approve', $projectDocument) }}" method="POST">
                                        @csrf
                                        <div class="bg-green-50 p-4 rounded border border-green-200">
                                            <h5 class="font-medium text-green-900 mb-2">Confirmar Aprobación Final</h5>
                                            <p class="text-sm text-green-800 mb-3">
                                                El Director ya aprobó este documento. Confirma la aprobación final para notificar al docente.
                                            </p>
                                            <div class="mb-3">
                                                <label for="approve_observation" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Observaciones (opcional)
                                                </label>
                                                <textarea name="observation" id="approve_observation" rows="3"
                                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                          placeholder="Comentarios adicionales sobre la aprobación final..."></textarea>
                                            </div>
                                            <button type="submit"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full"
                                                    onclick="return confirm('¿Estás seguro de confirmar la aprobación final de este documento?')">
                                                Confirmar Aprobación Final
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @elseif($directorDecision === 'denied')
                                <!-- Director denegó, mostrar confirmación de denegación -->
                                <div class="max-w-md mx-auto">
                                    <form action="{{ route('reviews.deny', $projectDocument) }}" method="POST">
                                        @csrf
                                        <div class="bg-red-50 p-4 rounded border border-red-200">
                                            <h5 class="font-medium text-red-900 mb-2">Confirmar Denegación Final</h5>
                                            <p class="text-sm text-red-800 mb-3">
                                                El Director denegó este documento. Confirma la denegación final para notificar al docente.
                                            </p>
                                            <div class="mb-3">
                                                <label for="deny_observation" class="block text-sm font-medium text-gray-800 mb-1">
                                                    Observaciones adicionales (opcional)
                                                </label>
                                                <textarea name="observation" id="deny_observation" rows="3"
                                                          class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring-red-500"
                                                          placeholder="Comentarios adicionales sobre la denegación final..."></textarea>
                                            </div>
                                            <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full"
                                                    onclick="return confirm('¿Estás seguro de confirmar la denegación final de este documento?')">
                                                Confirmar Denegación Final
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <!-- Fallback: mostrar ambas opciones si no se puede determinar la decisión del Director -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <form action="{{ route('reviews.approve', $projectDocument) }}" method="POST">
                                        @csrf
                                        <div class="bg-green-50 p-4 rounded border border-green-200">
                                            <h5 class="font-medium text-green-900 mb-2">Confirmar Aprobación Final</h5>
                                            <div class="mb-3">
                                                <label for="approve_observation" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Observaciones (opcional)
                                                </label>
                                                <textarea name="observation" id="approve_observation" rows="3"
                                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                          placeholder="Comentarios adicionales..."></textarea>
                                            </div>
                                            <button type="submit"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full"
                                                    onclick="return confirm('¿Estás seguro de confirmar la aprobación final?')">
                                                Confirmar Aprobación Final
                                            </button>
                                        </div>
                                    </form>

                                    <form action="{{ route('reviews.deny', $projectDocument) }}" method="POST">
                                        @csrf
                                        <div class="bg-red-50 p-4 rounded border border-red-200">
                                            <h5 class="font-medium text-red-900 mb-2">Confirmar Denegación Final</h5>
                                            <div class="mb-3">
                                                <label for="deny_observation" class="block text-sm font-medium text-gray-800 mb-1">
                                                    Observaciones (obligatorio)
                                                </label>
                                                <textarea name="observation" id="deny_observation" rows="3" required
                                                          class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring-red-500"
                                                          placeholder="Explica las razones de la denegación..."></textarea>
                                            </div>
                                            <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full"
                                                    onclick="return confirm('¿Estás seguro de confirmar la denegación final?')">
                                                Confirmar Denegación Final
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @else
                            <!-- Formularios normales para otras etapas -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Aprobar -->
                                <form action="{{ route('reviews.approve', $projectDocument) }}" method="POST">
                                    @csrf
                                    <div class="bg-green-50 p-4 rounded border border-green-200">
                                        <h5 class="font-medium text-green-900 mb-2">Aprobar Documento</h5>
                                        <div class="mb-3">
                                            <label for="approve_observation" class="block text-sm font-medium text-gray-700 mb-1">
                                                Observaciones (opcional)
                                            </label>
                                            <textarea name="observation" id="approve_observation" rows="3"
                                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                      placeholder="Comentarios adicionales sobre la aprobación..."></textarea>
                                        </div>
                                        <button type="submit"
                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full"
                                                onclick="return confirm('¿Estás seguro de aprobar este documento?')">
                                            Aprobar Documento
                                        </button>
                                    </div>
                                </form>

                                <!-- Denegar -->
                                <form action="{{ route('reviews.deny', $projectDocument) }}" method="POST">
                                    @csrf
                                    <div class="bg-red-50 p-4 rounded border border-red-200">
                                        <h5 class="font-medium text-red-900 mb-2">Denegar Documento (observación obligatoria)</h5>
                                        <div class="mb-3">
                                            <label for="deny_observation" class="block text-sm font-medium text-gray-800 mb-1">
                                                Observaciones (obligatorio)
                                            </label>
                                            <textarea name="observation" id="deny_observation" rows="3" required
                                                      class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring-red-500"
                                                      placeholder="Explica las razones de la denegación..."></textarea>
                                        </div>
                                         <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full"
                                                onclick="return confirm('¿Estás seguro de denegar este documento?')">
                                            Denegar Documento
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('reviews.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver a Lista de Revisión
                        </a>
                        <a href="{{ route('projects.show', $projectDocument->project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Ver Proyecto Completo
                        </a>
                        <a href="{{ route('dashboard') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

