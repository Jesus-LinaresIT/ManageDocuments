<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes y Métricas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Métricas generales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $totalProjects }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Proyectos</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalProjects }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $approvedDocuments }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Documentos Aprobados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $approvedDocuments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $pendingDocuments }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pendientes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $pendingDocuments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $deniedDocuments }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Denegados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $deniedDocuments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ $totalDocuments }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Documentos</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalDocuments }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Progreso por proyecto -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Progreso por Proyecto</h3>
                        <div class="space-y-4">
                            @foreach($projectProgress as $progress)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $progress['project']->name }}</h4>
                                        <span class="text-sm text-gray-500">{{ $progress['approved_count'] }}/{{ $progress['total_expected'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress['progress_percentage'] }}%"></div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $progress['progress_percentage'] }}% completado</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Documentos por estado -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Documentos por Estado</h3>
                        <div class="space-y-3">
                            @foreach($documentsByStatus as $status)
                                @php
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'sent' => 'Enviado',
                                        'approved_stage1' => 'Aprobado Etapa 1',
                                        'in_stage2' => 'En Etapa 2',
                                        'approved' => 'Aprobado',
                                        'denied' => 'Denegado',
                                    ];
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'sent' => 'bg-yellow-100 text-yellow-800',
                                        'approved_stage1' => 'bg-blue-100 text-blue-800',
                                        'in_stage2' => 'bg-purple-100 text-purple-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'denied' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$status->status] }}">
                                        {{ $statusLabels[$status->status] }}
                                    </span>
                                    <span class="font-semibold">{{ $status->count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métricas adicionales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                <!-- Tiempo promedio de revisión -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tiempo Promedio de Revisión</h3>
                        @if($averageReviewTime && $averageReviewTime->avg_hours)
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($averageReviewTime->avg_hours, 1) }} horas</p>
                            <p class="text-sm text-gray-600 mt-2">Tiempo promedio entre envío y aprobación</p>
                        @else
                            <p class="text-gray-500">No hay datos suficientes para calcular el tiempo promedio</p>
                        @endif
                    </div>
                </div>

                <!-- Tasa de denegación por etapa -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tasa de Denegación por Etapa</h3>
                        <div class="space-y-3">
                            @foreach($denialRates as $rate)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">
                                        {{ $rate->stage === 'stage1' ? 'Etapa 1 (Académica)' : 'Etapa 2 (Proyección Social)' }}
                                    </span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">{{ $rate->denied_count }}/{{ $rate->total_reviews }}</span>
                                        <span class="text-sm font-semibold text-red-600">{{ $rate->denial_rate }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actividad Reciente</h3>
                    <div class="space-y-3">
                        @foreach($recentActivity as $activity)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 text-xs font-bold">
                                            {{ $activity->user ? substr($activity->user->name, 0, 2) : 'S' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $activity->user ? $activity->user->name : 'Sistema' }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $activity->action }}</p>
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $activity->created_at->format('d/m H:i') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Navegación -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Volver al Dashboard
                </a>
                <a href="{{ route('audit.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Ver Auditoría
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

