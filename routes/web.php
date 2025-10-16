<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditController;
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
    Route::get('documents/{documentVersion}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Rutas de administración (solo administradores)
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('can:manage.users')
        ->group(function () {
            Route::resource('users', UserController::class);
        });


    // Rutas de revisión
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/{projectDocument}', [ReviewController::class, 'show'])->name('show');
        Route::post('/{projectDocument}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::post('/{projectDocument}/deny', [ReviewController::class, 'deny'])->name('deny');
    });

    // Rutas de reportes
    Route::prefix('reports')->name('reports.')->middleware('can:view.reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/project/{project}', [ReportController::class, 'projectDetails'])->name('project-details');
    });

    // Rutas de auditoría
    Route::prefix('audit')->name('audit.')->middleware('can:view.reports')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditController::class, 'show'])->name('show');
        Route::get('/export/csv', [AuditController::class, 'export'])->name('export');
    });

    // Rutas de administración de tipos de documentos
    Route::prefix('admin/document-types')->name('admin.document-types.')->middleware('can:manage.users')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::get('/{documentType}', [DocumentTypeController::class, 'show'])->name('show');
        Route::get('/{documentType}/edit', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
