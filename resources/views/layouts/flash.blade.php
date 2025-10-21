{{-- resources/views/layouts/flash.blade.php --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-3">
    @if(session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="rounded-md border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
            {{ session('warning') }}
        </div>
    @endif>

    @if(session('error'))
        <div class="rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- Compatibilidad con Breeze (session("status")) --}}
    @if(session('status') && !session('success') && !session('warning') && !session('error'))
        <div class="rounded-md border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3">
            {{ session('status') }}
        </div>
    @endif
</div>
