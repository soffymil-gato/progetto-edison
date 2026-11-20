<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Portal\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Reserved customer area. Route names are prefixed with "portal.".
| No public registration: accounts are created by the administration.
*/

Route::middleware('guest:customer')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth-form')->name('login.store');
    Route::get('/password/recupero', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/password/recupero', [ForgotPasswordController::class, 'store'])->middleware('throttle:password-email')->name('password.email');
    Route::get('/password/reimposta/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/password/reimposta', [ResetPasswordController::class, 'store'])->middleware('throttle:auth-form')->name('password.update');
    Route::get('/invito/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invito/{token}', [InvitationController::class, 'store'])->middleware('throttle:auth-form')->name('invitation.store');
});

Route::middleware('auth:customer')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/profilo', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profilo/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:auth-form')->name('profile.password');
});
