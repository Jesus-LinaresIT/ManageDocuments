<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // TODO: Implementar lógica de revisión en el 60% restante
    // Por ahora solo stubs como se especifica en los requisitos
    
    public function index()
    {
        // TODO: Lista de documentos pendientes de revisión
        return view('reviews.index');
    }

    public function show($id)
    {
        // TODO: Mostrar documento para revisión
        return view('reviews.show', compact('id'));
    }

    public function approve(Request $request, $id)
    {
        // TODO: Aprobar documento
        return back()->with('success', 'Documento aprobado (stub)');
    }

    public function deny(Request $request, $id)
    {
        // TODO: Denegar documento
        return back()->with('success', 'Documento denegado (stub)');
    }
}
