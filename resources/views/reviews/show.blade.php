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
                    <h3 class="text-lg font-medium mb-4">Revisión del documento #{{ $id }} (stub)</h3>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-yellow-800">
                            <strong>Nota:</strong> Esta es una vista temporal. La previsualización real del documento y la lógica de aprobación/denegación se implementará en el 60% restante del proyecto.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Información del Documento (Simulada)</h4>
                            <p class="text-sm text-gray-600">
                                <strong>ID:</strong> {{ $id }}<br>
                                <strong>Estado:</strong> Pendiente de revisión<br>
                                <strong>Tipo:</strong> Documento de prueba<br>
                                <strong>Fecha de envío:</strong> {{ now()->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-2">Área de Previsualización</h4>
                            <p class="text-sm text-blue-700">
                                Aquí se mostrará el contenido del documento para su revisión una vez implementada la funcionalidad completa.
                            </p>
                        </div>

                        <div class="flex space-x-4">
                            <form action="{{ route('reviews.approve', $id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('¿Estás seguro de aprobar este documento? (Stub)')">
                                    Aprobar Documento
                                </button>
                            </form>

                            <form action="{{ route('reviews.deny', $id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('¿Estás seguro de denegar este documento? (Stub)')">
                                    Denegar Documento
                                </button>
                            </form>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('reviews.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Volver a Lista de Revisión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
