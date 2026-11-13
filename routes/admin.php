<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContactRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
| Admin panel (gestionale). Route names are prefixed with "admin.".
| Every route is authorised on the server with a granular permission.
*/

Route::middleware('guest:staff')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:auth-form')->name('login.store');
    Route::get('/password/recupero', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/password/recupero', [ForgotPasswordController::class, 'store'])->middleware('throttle:password-email')->name('password.email');
    Route::get('/password/reimposta/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/password/reimposta', [ResetPasswordController::class, 'store'])->middleware('throttle:auth-form')->name('password.update');
    Route::get('/invito/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invito/{token}', [InvitationController::class, 'store'])->middleware('throttle:auth-form')->name('invitation.store');
});

Route::middleware('auth:staff')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/profilo', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profilo/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:auth-form')->name('profile.password');

    Route::middleware('can:contact_requests.view')->group(function (): void {
        Route::get('/richieste-contatto', [ContactRequestController::class, 'index'])->name('contact-requests.index');
        Route::get('/richieste-contatto/{contactRequest}', [ContactRequestController::class, 'show'])->name('contact-requests.show');
    });
    Route::patch('/richieste-contatto/{contactRequest}/gestita', [ContactRequestController::class, 'markHandled'])
        ->middleware('can:contact_requests.manage')->name('contact-requests.handled');

    Route::get('/utenti', [StaffUserController::class, 'index'])->middleware('can:users.view')->name('users.index');
    Route::middleware('can:users.manage')->group(function (): void {
        Route::get('/utenti/nuovo', [StaffUserController::class, 'create'])->name('users.create');
        Route::post('/utenti', [StaffUserController::class, 'store'])->name('users.store');
        Route::get('/utenti/{staffUser}/modifica', [StaffUserController::class, 'edit'])->name('users.edit');
        Route::put('/utenti/{staffUser}', [StaffUserController::class, 'update'])->name('users.update');
        Route::post('/utenti/{staffUser}/sospendi', [StaffUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/utenti/{staffUser}/riattiva', [StaffUserController::class, 'reactivate'])->name('users.reactivate');
        Route::post('/utenti/{staffUser}/invito', [StaffUserController::class, 'resendInvitation'])->name('users.invitation');
        Route::post('/utenti/{staffUser}/reset-password', [StaffUserController::class, 'sendPasswordReset'])->name('users.password-reset');
    });

    Route::middleware('can:roles.manage')->group(function (): void {
        Route::get('/ruoli', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/ruoli/nuovo', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/ruoli', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/ruoli/{role}/modifica', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/ruoli/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/ruoli/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('can:audit.view')->group(function (): void {
        Route::get('/registro-attivita', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/registro-attivita/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');
    });

    Route::middleware('can:settings.manage')->group(function (): void {
        Route::get('/impostazioni', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/impostazioni/{group}', [SettingsController::class, 'update'])->name('settings.update');
    });
});
