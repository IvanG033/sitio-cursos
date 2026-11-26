<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\InstallController;
use App\Models\Module;
use Illuminate\Support\Facades\Route;

// Página pública de inicio
Route::get('/', function () {
    $modules = Module::published()->ordered()->get(['id', 'title', 'summary', 'is_free']);

    return view('home', compact('modules'));
})->name('home');

// Instalación desde el navegador (solo si INSTALL_KEY está definida en .env)
Route::get('/instalar', [InstallController::class, 'show'])->name('install');
Route::post('/instalar', [InstallController::class, 'run'])->middleware('throttle:5,1');

// Registro, inicio de sesión y recuperación de contraseña
Route::middleware('guest')->group(function () {
    Route::get('/entrar', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/entrar', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::get('/olvide-mi-contrasena', [PasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/olvide-mi-contrasena', [PasswordController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/nueva-contrasena/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/nueva-contrasena', [PasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthController::class, 'logout'])->name('logout');
    Route::get('/en-revision', [AuthController::class, 'pending'])->name('pending');

    // Zona de alumnos (solo con acceso aprobado)
    Route::middleware('approved')->group(function () {
        Route::get('/mis-cursos', [CourseController::class, 'index'])->name('dashboard');
        Route::get('/modulo/{module}', [CourseController::class, 'show'])->name('modules.show');
        Route::post('/modulo/{module}/quiz', [CourseController::class, 'submitQuiz'])->name('modules.quiz');
        Route::post('/modulo/{module}/completar', [CourseController::class, 'complete'])->name('modules.complete');
        Route::get('/modulo/{module}/video', [CourseController::class, 'video'])->name('modules.video');
        Route::get('/modulo/{module}/archivo/{file}', [CourseController::class, 'download'])->name('modules.download');

        Route::get('/mensajes', [InboxController::class, 'index'])->name('inbox.index');
        Route::get('/mensajes/{message}', [InboxController::class, 'show'])->name('inbox.show');
    });

    // Administrador de contenidos
    Route::middleware(['approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/usuarios', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/usuarios/{user}/aprobar', [Admin\UserController::class, 'approve'])->name('users.approve');
        Route::post('/usuarios/{user}/bloquear', [Admin\UserController::class, 'block'])->name('users.block');
        Route::post('/usuarios/{user}/desbloquear', [Admin\UserController::class, 'unblock'])->name('users.unblock');
        Route::post('/usuarios/{user}/reiniciar', [Admin\UserController::class, 'resetProgress'])->name('users.reset');
        Route::delete('/usuarios/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/modulos', [Admin\ModuleController::class, 'index'])->name('modules.index');
        Route::get('/modulos/nuevo', [Admin\ModuleController::class, 'create'])->name('modules.create');
        Route::post('/modulos', [Admin\ModuleController::class, 'store'])->name('modules.store');
        Route::get('/modulos/{module}', [Admin\ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/modulos/{module}', [Admin\ModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modulos/{module}', [Admin\ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('/modulos/{module}/mover', [Admin\ModuleController::class, 'move'])->name('modules.move');
        Route::delete('/modulos/{module}/video', [Admin\ModuleController::class, 'removeVideo'])->name('modules.video.destroy');
        Route::delete('/modulos/{module}/archivo/{file}', [Admin\ModuleController::class, 'destroyFile'])->name('modules.files.destroy');

        Route::get('/mensajes', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('/mensajes/nuevo', [Admin\MessageController::class, 'create'])->name('messages.create');
        Route::post('/mensajes', [Admin\MessageController::class, 'store'])->name('messages.store');
    });
});
