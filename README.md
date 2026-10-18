# Rak Buku

Katalog buku pribadi berbasis web. Pengguna masuk dengan akun Google — tanpa
kata sandi sama sekali — mencatat buku yang sudah dibaca, memilih salah satu
dari empat gaya rak, lalu membagikan raknya lewat satu alamat:
`https://domainmu.com/username`.

Dibangun dengan Laravel 13, Inertia 3, Vue 3, dan Tailwind CSS 4.

---

## Fitur

- **Hanya masuk lewat Google** (Laravel Socialite). Aplikasi tidak menyimpan
  kata sandi, kode pemulihan, 2FA, atau passkey: tabel `users` tidak punya
  kolom `password`.
- **CRUD buku**: judul, penulis, penerbit, tahun, bahasa, status baca, rating
  bintang, sinopsis versi pengguna, review, tanggal mulai/selesai, dan unggah
  sampul depan. Ada batas 500 buku per rak.
- **Lima status baca**: ingin dibaca, sedang dibaca, sudah dibaca, ditunda,
  berhenti dibaca. Rak dikelompokkan otomatis mengikuti status.
- **Filter rak publik**: pencarian, penyaringan status, penyaringan bahasa,
  urutan, dan penanda favorit.
- **Empat tema rak**: Rak Kayu Klasik (`wood`), Rak Minimalis (`minimal`),
  Rak Neon (`neon`), dan Rak Kafe Hangat (`cozy`).
- **Tautan sosial** (Instagram, TikTok, X, YouTube, LinkedIn, situs web) diisi
  cukup dengan username.
- **Rak publik** di `/username` beserta halaman detail tiap buku di
  `/username/judul-buku`, lengkap dengan tautan berbagi native.
- **Landing page & halaman jelajah** Bahasa Indonesia yang mobile friendly.

---

## Kebutuhan

- PHP 8.4 dengan ekstensi `gd` atau `imagick` (untuk validasi gambar di tes)
- Composer 2
- Node.js 22+ dan npm
- SQLite (default) atau MySQL 8+

---

## Setup

```bash
composer run setup
```

Perintah itu menjalankan `composer install`, menyalin `.env`, membuat
application key, migrasi database, `npm install`, dan `npm run build`.

Setelah itu lengkapi:

```bash
php artisan storage:link   # hanya perlu kalau gambar disimpan di disk lokal
php artisan db:seed        # akun demo + 90-an buku contoh
```

Jalankan mode pengembangan (server, queue, Vite, dan log sekaligus):

```bash
composer run dev
```

Aplikasi tersedia di `http://localhost:8000`.

### Rak demo hasil seeding

Seeding membuat empat akun dengan tema rak berbeda supaya keempat tampilan rak
bisa dilihat langsung tanpa login.

| Username        | Nama            | Tema    | Alamat                                |
| --------------- | --------------- | ------- | ------------------------------------- |
| `demo`          | Alya Prameswari | wood    | `http://localhost:8000/demo`          |
| `rak-senja`     | Bagas Nugroho   | minimal | `http://localhost:8000/rak-senja`     |
| `neon-reader`   | Citra Larasati  | neon    | `http://localhost:8000/neon-reader`   |
| `kopi-dan-buku` | Damar Wicaksono | cozy    | `http://localhost:8000/kopi-dan-buku` |

Akun demo hanya bisa dilihat sebagai rak publik. Untuk masuk ke dashboard,
gunakan tombol **Lanjutkan dengan Google** dengan akun Google milikmu sendiri,
karena tidak ada kata sandi yang bisa dipakai untuk login.

---

## Menyiapkan login Google

Login Google adalah satu-satunya cara masuk, jadi kredensial ini wajib diisi
sebelum aplikasi bisa dipakai. Langkahnya:

1. Buka [Google Cloud Console](https://console.cloud.google.com/) lalu buat
   project (atau pilih yang sudah ada).
2. Masuk ke **APIs & Services → OAuth consent screen**, pilih tipe **External**,
   isi nama aplikasi dan email dukungan, lalu tambahkan scope `userinfo.email`
   dan `userinfo.profile`. Selama aplikasi masih berstatus _Testing_,
   tambahkan emailmu sebagai **Test user**.
3. Masuk ke **APIs & Services → Credentials → Create credentials → OAuth client
   ID**, pilih **Web application**.
4. Isi **Authorized JavaScript origins**:
    - `http://localhost:8000`
    - `https://domainmu.com` (untuk produksi)
5. Isi **Authorized redirect URIs**:
    - `http://localhost:8000/auth/google/callback`
    - `https://domainmu.com/auth/google/callback`
6. Salin **Client ID** dan **Client secret** ke `.env`:

```dotenv
GOOGLE_CLIENT_ID=xxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=/auth/google/callback
```

`GOOGLE_REDIRECT_URI` boleh berupa path relatif (default) maupun URL absolut
seperti `https://domainmu.com/auth/google/callback`. Nilai ini dibaca di
`config/services.php` dan dipakai `app/Http/Controllers/Auth/GoogleAuthController.php`.

7. Bersihkan cache konfigurasi lalu coba tombol **Lanjutkan dengan Google** di
   halaman masuk:

```bash
php artisan config:clear
```

Pengguna baru dari Google diarahkan ke `/onboarding/username` untuk memilih
username rak, karena username itulah yang menjadi alamat publiknya.

Foto profil Google otomatis diunduh dan disimpan saat pengguna belum punya
avatar (lewat `app/Actions/ImportRemoteAvatar.php`), lalu diproses seperti
avatar unggahan: diperkecil, dijadikan WebP, dan ditaruh di disk gambar yang
sedang dipakai. Foto yang diunggah sendiri tidak pernah ditimpa foto Google,
dan pengguna yang menghapus fotonya (memilih "tanpa foto") tidak akan dipasangi
ulang foto Google saat login kembali.

> Saat mengubah `.env` di produksi, jalankan `php artisan config:clear` (atau
> `config:cache`) agar nilai baru terpakai.

---

## Menyimpan sampul & avatar di Cloudflare R2

Secara default gambar disimpan di disk `public` lokal. Untuk menaruhnya di
Cloudflare R2, isi bagian `R2_*` di `.env` lalu ubah `BOOKSHELF_IMAGE_DISK`
menjadi `r2`:

```dotenv
R2_ACCESS_KEY_ID=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
R2_SECRET_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
R2_BUCKET=rak-buku
R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
R2_URL=https://pub-xxxxxxxxxxxx.r2.dev
R2_REGION=auto
BOOKSHELF_IMAGE_DISK=r2
```

Cara mengisinya:

1. Buat bucket di dashboard Cloudflare R2, lalu buka **Settings → Public
   access** dan aktifkan **r2.dev** (atau pasang domain sendiri). Alamat yang
   muncul itulah nilai `R2_URL`.
2. Buka **Manage R2 API Tokens → Create API token**, beri permission
   _Object Read & Write_, lalu salin Access Key ID dan Secret Access Key.
3. `R2_ENDPOINT` memakai Account ID dari halaman utama R2.

Catatan:

- Sampul & avatar dikonversi ke WebP dan diperkecil sebelum dikirim ke R2, jadi
  berkas yang tersimpan sudah ringan (lihat `app/Actions/StoreUploadedImage.php`).
- R2 mengabaikan ACL dan hanya mendukung checksum mode `COMPOSITE`, karena itu
  disk `r2` di `config/filesystems.php` tidak memakai `visibility` dan
  mematikan checksum bawaan AWS SDK (`request_checksum_calculation`). Akses
  publik diatur di pengaturan bucket, bukan per berkas.
- Kalau memakai R2, `php artisan storage:link` tidak diperlukan.

---

## Struktur penting

| Berkas                                                               | Isi                                                                                   |
| -------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| `config/bookshelf.php`                                               | Sumber tunggal daftar bahasa, aturan username, batas unggahan, dan paginasi           |
| `app/BookStatus.php`, `app/ShelfTheme.php`, `app/SocialPlatform.php` | Enum kosakata domain + `options()` untuk frontend                                     |
| `app/Http/Controllers/Shelf/`                                        | Halaman rak publik `/username` dan detail buku                                        |
| `routes/web.php`                                                     | Route publik, OAuth, onboarding, dan route `{username}` yang didaftarkan paling akhir |
| `resources/css/app.css`                                              | Token warna, mode gelap, dan variabel empat tema rak                                  |
| `resources/js/components/bookshelf/`                                 | Komponen UI yang dipakai ulang (sampul, badge status, rak, filter, form buku)         |
| `resources/js/pages/`                                                | Halaman Inertia (landing, jelajah, dashboard, rak, pengaturan, publik)                |
| `.ai/design-system.md`                                               | Panduan desain, inventaris komponen, dan konvensi frontend                            |
| `.ai/rules/`                                                         | Aturan singkat per area berkas yang dibaca agen/AI                                    |

---

## Perintah yang sering dipakai

```bash
composer run dev                 # server + queue + Vite + log
npm run build                    # build produksi aset frontend
npx vue-tsc --noEmit             # cek tipe TypeScript/Vue
npx vp check --fix               # lint + rapikan format frontend
vendor/bin/pint                  # rapikan format PHP
php artisan wayfinder:generate --with-form   # regenerasi helper route frontend
php artisan test --compact       # jalankan seluruh tes
php artisan test --compact tests/Feature/PublicShelfTest.php
vendor/bin/phpstan analyse       # analisis statis PHP
```

Setelah menambah atau mengganti nama route Laravel, jalankan
`php artisan wayfinder:generate --with-form` supaya helper di `@/routes` dan
`@/actions` ikut diperbarui. Build (`npm run build` / `npm run dev`) juga
menjalankannya otomatis.

---

## Membagikan rak

Alamat rak mengikuti username yang dipilih saat onboarding dan bisa diubah di
**Pengaturan → Profil**, tetapi paling cepat sekali setiap 3 hari (lihat
`cooldown_days` di `config/bookshelf.php`); sejak pilihan pertama saat
onboarding, waktu terakhir ganti disimpan di kolom `users.username_changed_at`.
Visibilitas diatur di **Pengaturan → Gaya rak**; selama opsi "Tampilkan rak ke
publik" dimatikan, `/username` hanya bisa dibuka oleh pemiliknya.
