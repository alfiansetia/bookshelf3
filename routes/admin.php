<?php

use App\Http\Controllers\Admin\BlockUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfanityScanController;
use App\Http\Controllers\Admin\UnblockUserController;
use App\Http\Controllers\Admin\UpdateUserRoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Area admin
|--------------------------------------------------------------------------
|
| Hanya untuk pengguna berperan admin. Dipakai untuk melihat daftar pengguna,
| memblokir akun beserta alasannya, dan mengatur peran.
|
*/

Route::middleware(['auth', 'not.blocked', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');

        Route::get('profanity', ProfanityScanController::class)->name('profanity.index');

        Route::patch('users/{user}/block', BlockUserController::class)->name('users.block');
        Route::delete('users/{user}/block', UnblockUserController::class)->name('users.unblock');
        Route::patch('users/{user}/role', UpdateUserRoleController::class)->name('users.role');
    });
