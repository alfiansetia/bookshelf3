---
paths:
    - 'resources/js/components/bookshelf/shelves/**'
---

# Shelves

## One component per shelf theme, styled by data-shelf-theme

Every shelf theme is (1) an enum case in app/ShelfTheme.php, (2) a `[data-shelf-theme='...']` variable block in resources/css/app.css, (3) an entry in resources/js/lib/shelfThemes.ts, and (4) a component in this folder that accepts the same `books`/`spotlight`/`favoriteSpotlight`/`owner` props and only uses shelf-* utilities. `spotlight` (BookStatus) dan `favoriteSpotlight` (boolean) tidak menyembunyikan apa pun: buku yang tidak kena hanya diredupkan via `spotlightDim()` dan yang kena ditegaskan via `spotlightActive()` dari resources/js/lib/shelfFilter.ts. ShelfRenderer wraps them and forces `.dark` for themes where isDark() is true (neon, story).

Pengecualian: warna milik data buku (bukan chrome tema) boleh non-shelf — punggung kayu dan sampul cadangan memakai `bookColor()` dari resources/js/lib/bookColors.ts (pilihan `cover_color` pemilik, fallback deterministik dari ID).

## Every theme is a different layout concept, not just a different palette

The shelf is one mixed list (`books`, already filtered); components must not regroup by status. Filtering lives in ShelfFilterBar, not in per-status sections.

- wood: buku berdiri berdampingan di atas papan kayu (judul ditulis vertikal di punggung, kartu pratinjau saat disorot) di desktop; daftar sampul kecil tersusun ke bawah di mobile.
- minimal: daftar indeks dengan nomor urut, sampul mungil, dan garis tipis; dua kolom di desktop, satu kolom di mobile. Tanpa papan atau bayangan rak.
- neon: kisi kotak berisi sampul penuh dengan garis pemindai dan pendar `--shelf-glow` di desktop; kartu horizontal tersusun ke bawah di mobile.
- cozy: papan tempel polaroid miring (`columns-*`) di desktop; satu kolom di mobile.
- story: satu strip reel horizontal berisi kartu vertikal full-cover dengan snap di semua ukuran layar; tombol panah melayang di tepi pada desktop, petunjuk geser pada mobile.

Jangan menyamakan struktur antar tema: kalau sebuah tema diubah jadi grid sampul yang sama seperti tema lain, konsepnya hilang.

## Shelf links and decorative previews

Tautan buku di rak selalu lewat `shelfBookHref(book, owner)` di resources/js/lib/shelfLinks.ts (halaman edit untuk pemilik, halaman publik untuk pengunjung). ShelfThemePreview.vue harus menggambarkan konsep tata letak temanya, karena komponen itu dipakai di pemilih gaya rak dan halaman depan.
