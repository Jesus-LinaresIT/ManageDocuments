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
                    <h3 class="text-lg font-medium mb-4">Revisión de documentos (stub)</h3>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-yellow-800">
                            <strong>Nota:</strong> Esta es una vista temporal. La lógica real de revisión de documentos se implementará en el 60% restante del proyecto.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <p class="text-gray-600">
                            Aquí se mostrará la lista de documentos pendientes de revisión una vez que se implemente la funcionalidad completa.
                        </p>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Lista temporal para pruebas:</h4>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('reviews.show', 1) }}" 
                                       class="text-blue-600 hover:text-blue-800 underline">
                                        Documento #1 (Prueba)
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
