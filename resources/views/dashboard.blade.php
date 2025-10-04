<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-6">Sistema de Gestión Documental - FICA/UTEC</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Proyectos -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-medium text-blue-900 mb-2">Proyectos</h4>
                            <p class="text-sm text-blue-700 mb-4">Gestiona tus proyectos de proyección social</p>
                            <a href="{{ route('projects.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Ver Proyectos
                            </a>
                        </div>

                        <!-- Administración -->
                        @can('manage.users')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <h4 class="text-lg font-medium text-green-900 mb-2">Administración</h4>
                            <p class="text-sm text-green-700 mb-4">Gestiona usuarios y roles del sistema</p>
                            <a href="{{ route('admin.users.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Gestionar Usuarios
                            </a>
                        </div>
                        @endcan

                        <!-- Revisión -->
                        @canany(['review.stage1', 'review.stage2'])
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h4 class="text-lg font-medium text-yellow-900 mb-2">Revisión</h4>
                            <p class="text-sm text-yellow-700 mb-4">Revisa documentos de proyectos</p>
                            <a href="{{ route('reviews.index') }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Ver Documentos
                            </a>
                        </div>
                        @endcan

                        <!-- Reportes -->
                        @can('view.reports')
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                            <h4 class="text-lg font-medium text-purple-900 mb-2">Reportes</h4>
                            <p class="text-sm text-purple-700 mb-4">Consulta reportes y estadísticas</p>
                            <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm opacity-50 cursor-not-allowed">
                                Reportes (Próximamente)
                            </button>
                        </div>
                        @endcan
                    </div>

                    <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-md font-medium text-gray-900 mb-2">Información del Usuario</h4>
                        <p class="text-sm text-gray-600">
                            <strong>Nombre:</strong> {{ auth()->user()->name }}<br>
                            <strong>Email:</strong> {{ auth()->user()->email }}<br>
                            <strong>Roles:</strong> 
                            @foreach(auth()->user()->roles as $role)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-1">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
