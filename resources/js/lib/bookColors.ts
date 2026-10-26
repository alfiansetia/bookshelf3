/**
 * Warna milik buku, bukan rak: dipakai punggung kayu dan sampul
 * cadangan supaya tiap buku tampil beda satu sama lain.
 */

/** Palet punggung ala buku sungguhan: berani tapi tetap enak dilihat. */
export const bookSpinePalette = [
    '#b91c1c',
    '#1d4ed8',
    '#047857',
    '#b45309',
    '#6d28d9',
    '#0e7490',
    '#be185d',
    '#4d7c0f',
    '#a16207',
    '#0f766e',
    '#9a3412',
    '#334155',
];

/** Pilihan cepat di form buku, termasuk warna aksen tiap tema rak. */
export const bookColorPresets = [
    '#b91c1c',
    '#9a3412',
    '#b45309',
    '#a16207',
    '#4d7c0f',
    '#047857',
    '#0e7490',
    '#1d4ed8',
    '#6d28d9',
    '#be185d',
    '#e08a9a',
    '#334155',
];

type ColoredBook = {
    id: number;
    cover_color?: string | null;
};

/**
 * Warna buku: pilihan pemilik dulu, kalau kosong ambil deterministik
 * dari ID supaya stabil antar render.
 */
export function bookColor(book: ColoredBook): string {
    if (book.cover_color) {
        return book.cover_color;
    }

    const index =
        Math.abs(book.id) % bookSpinePalette.length;

    return bookSpinePalette[index];
}

/**
 * Totol putih atau hitam untuk teks di atas warna buku.
 */
export function bookColorInk(hex: string): string {
    const { r, g, b } = hexToRgb(hex);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    return luminance > 0.6 ? '#1c1917' : '#ffffff';
}

/**
 * Gradasi sampul cadangan dari satu warna: terang di kiri atas,
 * gelap di kanan bawah. Pakai color-mix supaya ikut warna pilihan.
 */
export function bookColorGradient(hex: string): string {
    return `linear-gradient(135deg, ${hex}, color-mix(in srgb, ${hex} 55%, black))`;
}

function hexToRgb(hex: string): { r: number; g: number; b: number } {
    const clean = hex.replace('#', '');

    if (clean.length === 3) {
        const [r = '0', g = '0', b = '0'] = clean.split('');

        return {
            r: parseInt(r + r, 16),
            g: parseInt(g + g, 16),
            b: parseInt(b + b, 16),
        };
    }

    return {
        r: parseInt(clean.slice(0, 2), 16) || 0,
        g: parseInt(clean.slice(2, 4), 16) || 0,
        b: parseInt(clean.slice(4, 6), 16) || 0,
    };
}
