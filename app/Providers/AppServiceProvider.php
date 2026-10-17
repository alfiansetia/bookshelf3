<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Kode status yang ditampilkan memakai halaman galat milik aplikasi.
     *
     * @var list<int>
     */
    private const ERROR_STATUSES = [403, 404, 419, 429, 500, 503];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureErrorPages();
    }

    /**
     * Tampilkan halaman galat Inertia supaya seragam dengan tampilan aplikasi.
     *
     * Galat seperti 404 terjadi di luar middleware Inertia, jadi data bersama
     * (auth, nama aplikasi) diambil eksplisit lewat withSharedData().
     */
    protected function configureErrorPages(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): ?ExceptionResponse {
            $status = $response->statusCode();

            if (! in_array($status, self::ERROR_STATUSES, true)) {
                return null;
            }

            // Saat pengembangan, biarkan halaman debug bawaan Laravel tampil
            // agar jejak kesalahan server tetap bisa ditelusuri.
            if ($status === 500 && config('app.debug')) {
                return null;
            }

            return $response->render('errors/Error', ['status' => $status])->withSharedData();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );
    }
}
