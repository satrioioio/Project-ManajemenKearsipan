<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPhaseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');

    Route::middleware('role:admin,legal,pm')->group(function () {
        Route::get('/proyek-baru', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/proyek/{project}/ubah', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/proyek/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/proyek/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        Route::post('/proyek/{project}/fase', [ProjectPhaseController::class, 'store'])->name('phases.store');
        Route::put('/proyek/{project}/fase/{phase}', [ProjectPhaseController::class, 'update'])->name('phases.update');
        Route::delete('/proyek/{project}/fase/{phase}', [ProjectPhaseController::class, 'destroy'])->name('phases.destroy');
    });

    Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/dokumen/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/dokumen/{document}/unduh', [DocumentController::class, 'download'])->name('documents.download');

    Route::middleware('role:admin,legal,pm')->group(function () {
        Route::get('/dokumen-baru', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/dokumen', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/dokumen/{document}/ubah', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/dokumen/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::middleware('role:admin,legal')->group(function () {
        Route::post('/dokumen/{document}/kunci', [DocumentController::class, 'toggleLock'])->name('documents.toggle-lock');

        Route::get('/klasifikasi', [ClassificationController::class, 'index'])->name('classifications.index');
        Route::get('/klasifikasi-baru', [ClassificationController::class, 'create'])->name('classifications.create');
        Route::post('/klasifikasi', [ClassificationController::class, 'store'])->name('classifications.store');
        Route::get('/klasifikasi/{classification}/ubah', [ClassificationController::class, 'edit'])->name('classifications.edit');
        Route::put('/klasifikasi/{classification}', [ClassificationController::class, 'update'])->name('classifications.update');
        Route::delete('/klasifikasi/{classification}', [ClassificationController::class, 'destroy'])->name('classifications.destroy');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna-baru', [UserController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::get('/pengguna/{user}/ubah', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
