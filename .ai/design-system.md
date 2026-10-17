# Rak Buku — Design System & Frontend Handbook

Panduan ini adalah sumber tunggal untuk keputusan tampilan di aplikasi Rak Buku.
Aturan yang wajib diikuti agen sudah diringkas di `.ai/rules/`; dokumen ini
menjelaskan alasan dan cara memakainya.

---

## 1. Prinsip

1. **Kertas hangat, tinta hijau perpustakaan.** Semua halaman terasa seperti
   kertas buku, bukan dashboard SaaS dingin. Aksen amber dipakai hemat.
2. **Satu komponen, banyak konteks.** Satu `BookCover`, satu `StatusBadge`,
   satu `StarRating` dipakai di rak, dashboard, dan halaman publik. Jangan
   menduplikasi markup.
3. **Rak adalah bintangnya.** Chrome aplikasi (sidebar, header, filter) memakai
   warna netral; warna dan tekstur hanya diberikan kepada area rak.
4. **Mobile lebih dulu.** Semua grid satu kolom di layar kecil, dan target
   sentuh minimal 40×40 px.
5. **Bahasa manusia.** Copy Bahasa Indonesia, sapa dengan "kamu", kalimat
   pendek, tanpa hiperbola.

---

## 2. Token warna

Semua token dideklarasikan di `resources/css/app.css` sebagai custom property
dan dipetakan ke Tailwind lewat `@theme inline`. **Jangan pernah menulis warna
brand secara literal di komponen** — pakai utility semantik.

| Token                             | Terang                                  | Gelap                                 | Dipakai untuk                   |
| --------------------------------- | --------------------------------------- | ------------------------------------- | ------------------------------- |
| `--background`                    | `hsl(40 30% 98%)`                       | `hsl(200 12% 8%)`                     | Latar halaman                   |
| `--foreground`                    | `hsl(28 20% 12%)`                       | `hsl(40 20% 96%)`                     | Teks utama                      |
| `--card`                          | `hsl(0 0% 100%)`                        | `hsl(200 12% 11%)`                    | Permukaan kartu                 |
| `--muted` / `--muted-foreground`  | `hsl(38 26% 94.5%)` / `hsl(28 12% 42%)` | `hsl(200 9% 16%)` / `hsl(40 10% 68%)` | Label, keterangan, badge netral |
| `--primary`                       | `hsl(163 62% 25%)`                      | `hsl(160 52% 60%)`                    | Aksi utama, tautan, aksen hijau |
| `--accent`                        | `hsl(36 92% 52%)`                       | `hsl(36 88% 56%)`                     | Sorotan, statistik pilihan      |
| `--destructive`                   | `hsl(0 72% 48%)`                        | `hsl(0 65% 55%)`                      | Aksi hapus, error               |
| `--border` / `--input` / `--ring` | `hsl(36 20% 88%)` dll.                  | `hsl(200 9% 20%)` dll.                | Garis, outline focus            |

Utility: `bg-primary`, `text-primary`, `bg-card`, `text-muted-foreground`,
`border-border`, `ring-ring`, `bg-muted`, `text-destructive`.

### Warna status baca

Status tidak disimpan sebagai warna Tailwind, melainkan sebagai kunci semantik
(`amber`, `sky`, `emerald`, `violet`, `rose`) dari `app/BookStatus.php::color()`.
Kelasnya dipetakan literal di `resources/js/lib/statusStyles.ts` supaya terlihat
compiler Tailwind:

```ts
statusStyle('emerald').badge; // pil status
statusStyle(color).dot; // titik indikator / segmen bar
statusStyle(color).text; // teks berwarna
statusStyle(color).soft; // latar lembut
statusStyle(color).border; // border kartu terpilih
```

Kalau ada status baru: tambahkan case enum + `color()` + entri `statusStyles`
(atau pakai kunci warna yang sudah ada).

---

## 3. Tipografi

| Peran                        | Font             | Kelas                 |
| ---------------------------- | ---------------- | --------------------- |
| Judul & angka besar          | Instrument Serif | `font-display`        |
| Teks antarmuka               | Instrument Sans  | default (`font-sans`) |
| Angka monospaced (tema neon) | mono             | `font-mono`           |

Skala yang dipakai:

- Hero landing: `text-4xl sm:text-5xl lg:text-6xl` + `leading-[1.05]`
- Judul halaman/halaman publik: `text-3xl sm:text-4xl` (`font-display`)
- Judul section: `text-2xl sm:text-3xl` (`font-display`)
- Judul kartu: `text-sm`–`font-semibold` (jangan pakai display font)
- Eyebrow: `text-xs font-semibold tracking-[0.18em] uppercase text-primary`
- Teks bantu: `text-xs text-muted-foreground`
- Paragraf: `text-sm leading-relaxed text-muted-foreground`, pakai
  `text-pretty`/`text-balance` untuk blok panjang.

Label angka memakai `formatNumber()` (locale `id-ID`), tanggal memakai
`formatDate()` / `formatMonthYear()` / `formatDateRange()` dari
`resources/js/lib/format.ts`.

---

## 4. Bentuk, ruang, dan gerak

- Radius global `--radius: 0.75rem` → `rounded-lg` (kartu kecil), `rounded-2xl`
  (kartu & panel), `rounded-3xl` (hero, wadah rak).
- Kartu standar: `rounded-2xl border border-border/70 bg-card p-5 shadow-xs`.
  Hover interaktif: tambahkan `hover:shadow-md` (jangan mengubah ukuran).
- Jarak vertikal antar section: `space-y-6` di dalam halaman, `py-14 sm:py-16`
  untuk section landing.
- Transisi hanya untuk bayangan dan `translate` halus: `transition-shadow`,
  `duration-300`. Hormati `prefers-reduced-motion` (sudah ditangani
  `.book-lift`).
- Tidak ada glassmorphism berlebihan: `backdrop-blur` hanya untuk header
  lengket dan filter bar.

---

## 5. Sistem tema rak

Empat tema, masing-masing satu `enum case` di `app/ShelfTheme.php`:

| Nilai     | Nama            | Karakter                                      |
| --------- | --------------- | --------------------------------------------- |
| `wood`    | Rak Kayu Klasik | Papan kayu hangat, buku agak miring (default) |
| `minimal` | Rak Minimalis   | Garis tipis, banyak ruang kosong, netral      |
| `neon`    | Rak Neon        | Latar gelap + glow, memaksa `.dark`           |
| `cozy`    | Rak Kafe Hangat | Pastel, sudut sangat membulat                 |

Variabel per tema (di-set lewat `[data-shelf-theme='...']`):

```
--shelf-plank, --shelf-plank-edge, --shelf-surface, --shelf-surface-2,
--shelf-ink, --shelf-muted, --shelf-accent, --shelf-accent-soft,
--shelf-glow, --shelf-radius
```

Utility komponennya: `.shelf-surface`, `.shelf-plank`, `.book-lift`
(`--book-tilt` untuk kemiringan buku).

**Menambah tema baru** = 4 langkah:

1. `case` baru di `app/ShelfTheme.php` (label, tagline, description, isDark,
   accent, dan tambahkan ke `options()` otomatis).
2. Blok `[data-shelf-theme='...']` + varian `.dark` di `resources/css/app.css`.
3. Entri di `resources/js/lib/shelfThemes.ts` (komponen + kelas latar).
4. Komponen rak di `resources/js/components/bookshelf/shelves/` yang hanya
   memakai utility `shelf-*`, lalu daftarkan di `ShelfRenderer` lewat map tadi.

Komponen rak menerima props seragam `{ rows: ShelfRow[]; owner?: boolean }` dan
memilih tautan: pemilik → halaman ubah buku, pengunjung → `book.public_url`.

---

## 6. Inventaris komponen

Semua di `resources/js/components/bookshelf/`. Cek daftar ini sebelum menulis
komponen baru.

**Data & identitas**

- `BookCover.vue` — sampul dengan rasio 2:3, punggung buku opsional, dan
  sampul cadangan bergradien + inisial judul saat gambar gagal/kosong.
  Ukuran: `xs | sm | md | lg | xl`.
- `StatusBadge.vue` — pil status; isi `statuses` bila hanya punya nilai status.
- `StarRating.vue` — tampilan rating (mendukung setengah bintang).
- `StarRatingInput.vue` — input rating untuk form.
- `SocialIcon.vue` — SVG merek (instagram, x, youtube, linkedin, tiktok,
  google, fallback globe). Lucide tidak punya ikon merek.
- `SocialLinks.vue` — daftar lencana tautan sosial.

**Struktur halaman**

- `SectionHeading.vue` — eyebrow + judul + deskripsi (+ slot aksi).
- `EmptyState.vue` — keadaan kosong dengan ikon dan slot tombol.
- `ProfileHero.vue` — header rak publik (avatar, statistik, sosial, share).
- `StatCard.vue` — satu angka besar; `tone: default | primary | accent`.

**Rak**

- `shelves/ShelfRenderer.vue` — pembungkus: set `data-shelf-theme`, paksa
  `.dark` untuk tema gelap, teruskan `rows`/`owner`.
- `shelves/WoodShelf.vue`, `MinimalShelf.vue`, `NeonShelf.vue`,
  `CozyShelf.vue`.
- `ShelfThemePreview.vue` — ilustrasi mini untuk pemilih tema.
- `ShelfThemePicker.vue` — radiogroup pemilihan tema.

**Filter & navigasi**

- `ShelfFilterBar.vue` — pencarian (debounce 350 ms), filter bahasa, urutan,
  chip status, chip favorit, reset. Melakukan kunjungan Inertia sendiri ke
  `props.url`.
- `ShelfPagination.vue` — paginasi gaya aplikasi (menerima `Paginated<T>`).
- `ShareButton.vue` — `navigator.share` dengan fallback salin tautan.

**Form & aksi**

- `CoverUploadField.vue` — unggah gambar dengan pratinjau, validasi tipe/ukuran
  lokal, dan `aspect: 'cover' | 'avatar'`.
- `BookForm.vue` — form buku lengkap (tambah & ubah) memakai `useForm` Inertia,
  sudah termasuk unggah sampul, pilih status, rating, dan tanggal.
- `BookCard.vue` — kartu buku untuk grid dashboard/daftar rak. `owner` menampilkan
  tombol Ubah/Hapus, pengunjung mendapat tautan detail.

**Kerangka publik**

- `PublicHeader.vue` (navigasi + menu mobile), `PublicFooter.vue`,
  `resources/js/layouts/PublicLayout.vue`.

**Primitif UI** — `resources/js/components/ui/*` (button, input, label, select,
textarea, switch, card, dialog, sheet, sidebar, …). Ini komponen vendored:
**jangan diedit**, bungkus atau lengkapi lewat props.

---

## 7. Tata letak halaman

Layout ditentukan `resources/js/app.ts` berdasarkan nama halaman:

| Halaman                          | Layout                                  |
| -------------------------------- | --------------------------------------- |
| `Welcome`, `Explore`, `public/*` | `PublicLayout` (header + footer publik) |
| `auth/*`, `onboarding/*`         | `AuthLayout`                            |
| `settings/*`                     | `[AppLayout, SettingsLayout]`           |
| lainnya (`Dashboard`, `books/*`) | `AppLayout` (sidebar)                   |

Halaman mengirim props layout lewat `defineOptions({ layout: { ... } })`:

```vue
defineOptions({ layout: { breadcrumbs: [{ title: 'Rak saya', href: booksIndex()
}] }, });
```

Kontainer standar:

- Area aplikasi: `<div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">`
- Halaman publik: `<div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-8 sm:px-6">`
- Baca/simpan: panel `rounded-2xl border border-border/70 bg-card p-5 shadow-xs`.

---

## 8. Pola form & navigasi

- Form memakai `useForm` dari `@inertiajs/vue3` + `form.post/put/patch(url)`
  memakai helper Wayfinder (`store.url()`, `update.url(book.id)`).
- Unggah berkas: kirim sebagai `FormData`; Inertia otomatis memakai
  `_method` spoofing untuk PUT/PATCH.
- Kesalahan validasi ditampilkan dengan `<InputError :message="form.errors.x" />`
  tepat di bawah field, plus ringkasan `form.hasErrors` bila perlu.
- Umpan balik sukses dari server memakai flash toast
  (`Inertia::flash('toast', ['type' => 'success', 'message' => '...'])`) yang
  ditangkap `resources/js/lib/flashToast.ts` → `vue-sonner`.
- Navigasi & filter memakai kunjungan Inertia dengan
  `{ preserveScroll: true, preserveState: true }`.
- URL tidak pernah ditulis manual: pakai helper dari `@/routes` / `@/actions`
  hasil `php artisan wayfinder:generate --with-form`.

---

## 9. Aksesibilitas

- Setiap kontrol punya `Label` yang terhubung (`for`/`id`) atau `aria-label`.
- Status tidak boleh dibedakan hanya lewat warna: sertakan label teks dan ikon.
- Fokus terlihat: biarkan `focus-visible:ring-*` bawaan komponen UI.
- Gambar dekoratif `aria-hidden="true"`; sampul buku memakai `alt="Sampul {judul}"`.
- Semua tombol hanya-ikon memuat `sr-only` atau `aria-label`.

---

## 10. Daftar periksa sebelum menyelesaikan tugas frontend

1. Cek ulang apakah komponen serupa sudah ada di `components/bookshelf/`.
2. Pastikan tidak ada warna brand literal dan tidak ada URL manual.
3. `npx vue-tsc --noEmit` hijau.
4. `npx vp check --fix` tidak menyisakan masalah.
5. `npm run build` sukses.
6. Perubahan perilaku disertai tes di `tests/Feature`, dijalankan dengan
   `php artisan test --compact`.
