<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Proyecto') }}
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

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">{{ $project->name }}</h3>
                        <p class="text-sm text-gray-500">Período: {{ $project->period }} | Unidad: {{ $project->unit }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Docente</h4>
                            <p class="text-sm text-gray-600">{{ $project->teacher->name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Revisor Académico</h4>
                            <p class="text-sm text-gray-600">{{ $project->revAcademic->name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Revisor Social</h4>
                            <p class="text-sm text-gray-600">{{ $project->revSocial->name }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-lg font-medium mb-4">Estado de Documentos</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actualización</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($project->projectDocuments as $projectDocument)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $projectDocument->documentType->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$projectDocument->status] }}">
                                                    {{ $statusLabels[$projectDocument->status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $projectDocument->updated_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if(auth()->user()->id === $project->teacher_id)
                                                    <a href="{{ route('projects.docs', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        Gestionar
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">Solo docente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver a Proyectos
                        </a>
                        @if(auth()->user()->id === $project->teacher_id)
                            <a href="{{ route('projects.docs', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Gestionar Documentos
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


