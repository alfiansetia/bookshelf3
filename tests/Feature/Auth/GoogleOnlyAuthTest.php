<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Autentikasi aplikasi ini hanya lewat Google, tanpa kata sandi.
 */
class GoogleOnlyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabel_users_tidak_menyimpan_kata_sandi(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'password'));
        $this->assertFalse(Schema::hasColumn('users', 'two_factor_secret'));
        $this->assertFalse(Schema::hasTable('password_reset_tokens'));
        $this->assertFalse(Schema::hasTable('passkeys'));
    }

    public function test_halaman_masuk_menampilkan_tombol_google(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/Login'));
    }

    public function test_halaman_daftar_menampilkan_tombol_google(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/Register'));
    }

    public function test_pengunjung_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_tidak_ada_jalur_login_memakai_kata_sandi(): void
    {
        $this->post(route('login.store'), [
            'email' => 'demo@bookshelf.test',
            'password' => 'password',
        ])->assertRedirect(route('auth.google.redirect'));

        $this->assertGuest();
    }

    public function test_pengguna_yang_sudah_masuk_diarahkan_ke_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_pengguna_dapat_keluar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
