const numberFormatter = new Intl.NumberFormat('id-ID');

/**
 * Angka dengan pemisah ribuan gaya Indonesia.
 */
export function formatNumber(value: number | null | undefined): string {
    if (value === null || value === undefined || Number.isNaN(value)) {
        return '0';
    }

    return numberFormatter.format(value);
}

/**
 * Tanggal lengkap, contoh: 12 Maret 2024.
 */
export function formatDate(value: string | null | undefined): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}

/**
 * Bulan dan tahun, contoh: Maret 2024.
 */
export function formatMonthYear(
    value: string | null | undefined,
): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric',
    }).format(date);
}

/**
 * Rentang tahun baca, contoh: "12 Jan 2024 - 3 Feb 2024".
 */
export function formatDateRange(
    start: string | null | undefined,
    end: string | null | undefined,
): string | null {
    const from = formatDate(start);
    const to = formatDate(end);

    if (from && to) {
        return `${from} - ${to}`;
    }

    return from ?? (to ? `Selesai ${to}` : null);
}

/**
 * Jarak waktu relatif, contoh: "3 hari lalu".
 */
export function formatRelative(
    value: string | null | undefined,
): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const units: [Intl.RelativeTimeFormatUnit, number][] = [
        ['year', 60 * 60 * 24 * 365],
        ['month', 60 * 60 * 24 * 30],
        ['week', 60 * 60 * 24 * 7],
        ['day', 60 * 60 * 24],
        ['hour', 60 * 60],
        ['minute', 60],
    ];

    const formatter = new Intl.RelativeTimeFormat('id-ID', {
        numeric: 'auto',
    });

    for (const [unit, secondsInUnit] of units) {
        if (Math.abs(seconds) >= secondsInUnit) {
            return formatter.format(Math.round(seconds / secondsInUnit), unit);
        }
    }

    return 'baru saja';
}

/**
 * Jumlah buku dengan satuan yang benar.
 */
export function bookCount(value: number): string {
    return `${formatNumber(value)} buku`;
}

/**
 * Rating dalam bentuk teks, contoh "4,5 dari 5".
 */
export function ratingLabel(rating: number | null | undefined): string {
    if (!rating) {
        return 'Belum diberi rating';
    }

    return `${formatNumber(rating)} dari 5`;
}

/**
 * Waktu baca kasar berdasarkan jumlah halaman.
 */
export function readingTime(pages: number | null | undefined): string | null {
    if (!pages || pages <= 0) {
        return null;
    }

    const minutes = Math.round(pages * 1.6);

    if (minutes < 60) {
        return `± ${minutes} menit`;
    }

    const hours = Math.round(minutes / 60);

    return `± ${hours} jam`;
}

/**
 * Gradien deterministik untuk sampul cadangan.
 */
const coverGradients = [
    'from-emerald-600 to-emerald-900',
    'from-amber-500 to-orange-800',
    'from-sky-600 to-indigo-900',
    'from-rose-500 to-rose-900',
    'from-violet-600 to-violet-900',
    'from-teal-500 to-cyan-900',
    'from-stone-600 to-stone-900',
    'from-fuchsia-600 to-purple-900',
];

export function coverGradient(seed: string): string {
    let hash = 0;

    for (let index = 0; index < seed.length; index++) {
        hash = (hash * 31 + seed.charCodeAt(index)) % 100000;
    }

    return coverGradients[hash % coverGradients.length];
}

/**
 * Inisial dari judul buku untuk sampul cadangan.
 */
export function titleInitials(title: string): string {
    const words = title
        .trim()
        .split(/\s+/u)
        .filter((word) => word.length > 2);

    if (words.length === 0) {
        return title.slice(0, 2).toUpperCase();
    }

    const first = Array.from(words[0])[0] ?? '';
    const second = Array.from(words[words.length - 1])[0] ?? '';

    return `${first}${second}`.toUpperCase();
}
