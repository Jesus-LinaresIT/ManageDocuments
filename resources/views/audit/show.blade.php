<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de Auditoría') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Registro de Actividad #{{ $auditLog->id }}</h3>
                        <p class="text-sm text-gray-500">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900 mb-2">Información Básica</h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Usuario:</span>
                                    <span class="text-sm text-gray-900 ml-2">
                                        {{ $auditLog->user ? $auditLog->user->name : 'Sistema' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Acción:</span>
                                    <span class="text-sm text-gray-900 ml-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $auditLog->action }}
                                        </span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Fecha:</span>
                                    <span class="text-sm text-gray-900 ml-2">
                                        {{ $auditLog->created_at->format('d/m/Y H:i:s') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900 mb-2">Contexto</h4>
                            <div class="space-y-2">
                                @if($auditLog->user)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Email:</span>
                                        <span class="text-sm text-gray-900 ml-2">{{ $auditLog->user->email }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Roles:</span>
                                        <span class="text-sm text-gray-900 ml-2">
                                            @foreach($auditLog->user->roles as $role)
                                                <span class="px-1 py-0.5 text-xs bg-gray-200 text-gray-800 rounded mr-1">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </span>
                                    </div>
                                @else
                                    <div>
                                        <span class="text-sm text-gray-500">Acción del sistema</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($auditLog->meta)
                        <div class="mb-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Metadatos</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @php
                                    $meta = is_string($auditLog->meta) ? json_decode($auditLog->meta, true) : $auditLog->meta;
                                @endphp
                                
                                @if(is_array($meta) && count($meta) > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($meta as $key => $value)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-500 capitalize">
                                                    {{ str_replace('_', ' ', $key) }}:
                                                </span>
                                                <span class="text-sm text-gray-900 mt-1">
                                                    @if(is_array($value))
                                                        <pre class="text-xs bg-white p-2 rounded border">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500">No hay metadatos adicionales</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Información adicional según el tipo de acción -->
                    @if($auditLog->action === 'project.create')
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="font-medium text-green-900 mb-2">Proyecto Creado</h4>
                            <p class="text-sm text-green-800">
                                Se creó un nuevo proyecto en el sistema.
                            </p>
                        </div>
                    @elseif($auditLog->action === 'document.upload')
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-2">Documento Subido</h4>
                            <p class="text-sm text-blue-800">
                                Se subió una nueva versión de un documento.
                            </p>
                        </div>
                    @elseif($auditLog->action === 'document.approved')
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="font-medium text-green-900 mb-2">Documento Aprobado</h4>
                            <p class="text-sm text-green-800">
                                Un documento fue aprobado en el proceso de revisión.
                            </p>
                        </div>
                    @elseif($auditLog->action === 'document.denied')
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="font-medium text-red-900 mb-2">Documento Denegado</h4>
                            <p class="text-sm text-red-800">
                                Un documento fue denegado y requiere correcciones.
                            </p>
                        </div>
                    @elseif($auditLog->action === 'document.viewed')
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="font-medium text-yellow-900 mb-2">Documento Visualizado</h4>
                            <p class="text-sm text-yellow-800">
                                Un revisor visualizó un documento para su revisión.
                            </p>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('audit.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver a Auditoría
                        </a>
                        <a href="{{ route('reports.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
