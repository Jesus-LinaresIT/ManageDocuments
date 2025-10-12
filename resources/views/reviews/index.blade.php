<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Revisión de Documentos') }}
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

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Documentos Pendientes de Revisión</h3>
                        <div class="text-sm text-gray-500">
                            @if(auth()->user()->hasRole('Revisor Académico'))
                                Etapa 1 - Revisión Académica
                            @elseif(auth()->user()->hasRole('Revisor Proyección Social'))
                                Etapa 2 - Revisión de Proyección Social
                            @else
                                Todas las Etapas
                            @endif
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proyecto</label>
                                <select name="project_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos los proyectos</option>
                                    @foreach(\App\Models\Project::all() as $project)
                                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todos los estados</option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviado</option>
                                    <option value="approved_stage1" {{ request('status') == 'approved_stage1' ? 'selected' : '' }}>Aprobado Etapa 1</option>
                                    <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denegado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filtrar
                            </button>
                            <a href="{{ route('reviews.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Limpiar
                            </a>
                        </div>
                    </form>

                    @if($documents->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyecto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Docente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actualización</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($documents as $document)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $document->project->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->documentType->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->project->teacher->name }}
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
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$document->status] }}">
                                                    {{ $statusLabels[$document->status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->updated_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('reviews.show', $document) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Revisar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $documents->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No hay documentos pendientes de revisión.</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

