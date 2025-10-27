<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Proyecto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                    <form action="{{ route('projects.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Proyecto</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>

                            <div>
                                <label for="period" class="block text-sm font-medium text-gray-700">Período</label>
                                <select name="period" id="period" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Seleccionar período</option>
                                    <option value="Ciclo1" {{ old('period') == 'Ciclo1' ? 'selected' : '' }}>Ciclo1</option>
                                    <option value="Ciclo2" {{ old('period') == 'Ciclo2' ? 'selected' : '' }}>Ciclo2</option>
                                </select>
                            </div>

                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unidad</label>
                                <input type="hidden" name="unit" value="FICA">
                                <input type="text" value="FICA" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                            </div>

                            <div>
                                <label for="target_date" class="block text-sm font-medium text-gray-700">Fecha Objetivo</label>
                                <input type="date" name="target_date" id="target_date" value="{{ old('target_date') }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>

                            <div>
                                <label for="teacher_id" class="block text-sm font-medium text-gray-700">Docente</label>
                                <select name="teacher_id" id="teacher_id" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Seleccionar docente</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="rev_academic_id" class="block text-sm font-medium text-gray-700">Coordinador de Proyección Social</label>
                                <input type="hidden" name="rev_academic_id" value="{{ $preloadedData['rev_academic_id'] ?? '' }}">
                                <input type="text" value="{{ $preloadedData['rev_academic_name'] ?? 'No disponible' }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                            </div>

                            <div>
                                <label for="rev_social_id" class="block text-sm font-medium text-gray-700">Director de Proyección Social</label>
                                <input type="hidden" name="rev_social_id" value="{{ $preloadedData['rev_social_id'] ?? '' }}">
                                <input type="text" value="{{ $preloadedData['rev_social_name'] ?? 'Lic. William Antonio Geliz' }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-3">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Crear Proyecto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


