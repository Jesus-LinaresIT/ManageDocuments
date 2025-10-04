<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Información Personal</h4>
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>Nombre:</strong> {{ $user->name }}<br>
                                <strong>Email:</strong> {{ $user->email }}<br>
                                <strong>Creado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}<br>
                                <strong>Última actualización:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="font-medium text-gray-900">Roles Asignados</h4>
                            <div class="mt-2">
                                @if($user->roles->count() > 0)
                                    @foreach($user->roles as $role)
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-2 mb-2">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500">Sin roles asignados</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Volver a Usuarios
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Editar Usuario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
