<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\TotpController;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\RequireAdmin;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/2fa', [AuthController::class, 'showTotp'])->name('login.totp');
Route::post('/login/2fa', [AuthController::class, 'verifyTotp'])->name('login.totp.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(RequireAuth::class)->group(function () {
    Route::get('/', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
    Route::delete('/files/{file}', [GalleryController::class, 'destroy'])->name('files.destroy');
    Route::get('/profile/password', [ProfileController::class, 'showChangePassword'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password.update');
});

// Admin routes
Route::middleware(RequireAdmin::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/password', [UserController::class, 'showChangePassword'])->name('users.password');
    Route::post('/users/{user}/password', [UserController::class, 'changePassword'])->name('users.password.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/files', [AdminFileController::class, 'index'])->name('files.index');
    Route::delete('/files/{file}', [AdminFileController::class, 'destroy'])->name('files.destroy');
    Route::patch('/files/{file}/owner', [AdminFileController::class, 'changeOwner'])->name('files.owner');

    Route::get('/users/{user}/2fa', [TotpController::class, 'show'])->name('users.2fa');
    Route::post('/users/{user}/2fa/generate', [TotpController::class, 'generate'])->name('users.2fa.generate');
    Route::post('/users/{user}/2fa/verify', [TotpController::class, 'verify'])->name('users.2fa.verify');
    Route::post('/users/{user}/2fa/disable', [TotpController::class, 'disable'])->name('users.2fa.disable');
    Route::post('/users/{user}/2fa/reset', [TotpController::class, 'reset'])->name('users.2fa.reset');
});
