<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ShelfController;
use App\Http\Controllers\Settings\SocialLinksController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.blocked'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::inertia('settings/security', 'settings/Security')->name('security.edit');
    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});

Route::middleware(['auth', 'not.blocked', 'shelf.username'])->group(function () {
    Route::get('settings/shelf', [ShelfController::class, 'edit'])->name('shelf.edit');
    Route::patch('settings/shelf', [ShelfController::class, 'update'])->name('shelf.update');

    Route::get('settings/socials', [SocialLinksController::class, 'edit'])->name('socials.edit');
    Route::patch('settings/socials', [SocialLinksController::class, 'update'])->name('socials.update');
});
