<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Tipo de Documento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.document-types.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Tipo de Documento *
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Ej: Propuesta de Proyecto">
                            </div>

                            <div>
                                <label for="sequence" class="block text-sm font-medium text-gray-700 mb-1">
                                    Secuencia *
                                </label>
                                <input type="number" name="sequence" id="sequence" value="{{ old('sequence') }}" required min="1" max="10"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="1">
                                <p class="text-xs text-gray-500 mt-1">Orden en el flujo de documentos (1-10)</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipos de Archivo Permitidos *
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="allowed_mime[]" value="application/pdf" 
                                           {{ in_array('application/pdf', old('allowed_mime', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">PDF (.pdf)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="allowed_mime[]" value="application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
                                           {{ in_array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', old('allowed_mime', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Word (.docx)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="allowed_mime[]" value="application/msword" 
                                           {{ in_array('application/msword', old('allowed_mime', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Word (.doc)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="allowed_mime[]" value="image/jpeg" 
                                           {{ in_array('image/jpeg', old('allowed_mime', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">JPEG (.jpg)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="allowed_mime[]" value="image/png" 
                                           {{ in_array('image/png', old('allowed_mime', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">PNG (.png)</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="max_mb" class="block text-sm font-medium text-gray-700 mb-1">
                                Tamaño Máximo (MB) *
                            </label>
                            <input type="number" name="max_mb" id="max_mb" value="{{ old('max_mb', 20) }}" required min="1" max="100"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Tamaño máximo permitido en megabytes (1-100 MB)</p>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <a href="{{ route('admin.document-types.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Crear Tipo de Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

