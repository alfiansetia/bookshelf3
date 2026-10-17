<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Onboarding\UsernameAvailabilityController;
use App\Http\Controllers\Onboarding\UsernameController;
use App\Http\Controllers\Shelf\PublicBookController;
use App\Http\Controllers\Shelf\PublicProfileController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('home');
Route::get('jelajah', ExploreController::class)->name('explore');

/*
|--------------------------------------------------------------------------
| Masuk & daftar (hanya lewat Google)
|--------------------------------------------------------------------------
|
| Aplikasi ini tidak menyimpan password sama sekali. Halaman masuk dan
| daftar hanya berisi tombol Google, dan POST /login dialihkan kembali ke
| Google agar tidak ada jalur login memakai kata sandi.
|
*/

Route::middleware('guest')->group(function () {
    Route::inertia('login', 'auth/Login')->name('login');
    Route::inertia('register', 'auth/Register')->name('register');

    Route::post('login', fn (): RedirectResponse => to_route('auth.google.redirect'))
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Login dengan Google
|--------------------------------------------------------------------------
*/

Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])
    ->name('auth.google.redirect');

Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');

Route::post('logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Onboarding username
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('onboarding/username', [UsernameController::class, 'edit'])
        ->name('onboarding.username.edit');

    Route::put('onboarding/username', [UsernameController::class, 'update'])
        ->name('onboarding.username.update');

    Route::get('onboarding/username/availability', UsernameAvailabilityController::class)
        ->middleware('throttle:40,1')
        ->name('onboarding.username.availability');
});

/*
|--------------------------------------------------------------------------
| Area pengguna yang sudah login
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'shelf.username'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('books', BookController::class)->except('show');
});

require __DIR__.'/settings.php';

/*
|--------------------------------------------------------------------------
| Rak publik: example.com/username
|--------------------------------------------------------------------------
|
| Selalu didaftarkan paling akhir agar tidak menabrak route aplikasi di atas.
|
*/

Route::get('{username}', PublicProfileController::class)
    ->where('username', '[A-Za-z0-9][A-Za-z0-9._-]*')
    ->name('shelf.show');

Route::get('{username}/{book}', PublicBookController::class)
    ->where([
        'username' => '[A-Za-z0-9][A-Za-z0-9._-]*',
        'book' => '[a-z0-9][a-z0-9-]*',
    ])
    ->name('shelf.book.show');
