<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de proyectos
    Route::resource('projects', ProjectController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('projects/{project}/docs', [DocumentController::class, 'index'])->name('projects.docs');
    Route::post('projects/{project}/docs/{documentType}/upload', [DocumentController::class, 'upload'])->name('projects.docs.upload');

    // Rutas de administración (solo administradores)
    Route::middleware('can:manage.users')->group(function () {
        Route::resource('admin/users', UserController::class);
    });

    // Rutas de revisión (stubs)
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::post('/{id}/deny', [ReviewController::class, 'deny'])->name('deny');
    });
});

require __DIR__.'/auth.php';
