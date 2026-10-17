import type { BookStatus, StatusColor } from '@/types';

type StatusStyle = {
    /** Kelas untuk badge/pil status. */
    badge: string;
    /** Kelas untuk titik penanda kecil. */
    dot: string;
    /** Kelas untuk teks berwarna. */
    text: string;
    /** Kelas latar lembut untuk kartu/aksen. */
    soft: string;
    /** Kelas border untuk kartu terpilih. */
    border: string;
};

/**
 * Kelas Tailwind ditulis literal (bukan disusun dari string) supaya
 * tetap terdeteksi oleh compiler Tailwind.
 */
export const statusStyles: Record<StatusColor, StatusStyle> = {
    amber: {
        badge: 'bg-amber-100 text-amber-900 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-500/25',
        dot: 'bg-amber-500',
        text: 'text-amber-700 dark:text-amber-300',
        soft: 'bg-amber-50 dark:bg-amber-500/10',
        border: 'border-amber-300 dark:border-amber-500/40',
    },
    sky: {
        badge: 'bg-sky-100 text-sky-900 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-200 dark:ring-sky-500/25',
        dot: 'bg-sky-500',
        text: 'text-sky-700 dark:text-sky-300',
        soft: 'bg-sky-50 dark:bg-sky-500/10',
        border: 'border-sky-300 dark:border-sky-500/40',
    },
    emerald: {
        badge: 'bg-emerald-100 text-emerald-900 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-200 dark:ring-emerald-500/25',
        dot: 'bg-emerald-500',
        text: 'text-emerald-700 dark:text-emerald-300',
        soft: 'bg-emerald-50 dark:bg-emerald-500/10',
        border: 'border-emerald-300 dark:border-emerald-500/40',
    },
    violet: {
        badge: 'bg-violet-100 text-violet-900 ring-violet-200 dark:bg-violet-500/15 dark:text-violet-200 dark:ring-violet-500/25',
        dot: 'bg-violet-500',
        text: 'text-violet-700 dark:text-violet-300',
        soft: 'bg-violet-50 dark:bg-violet-500/10',
        border: 'border-violet-300 dark:border-violet-500/40',
    },
    rose: {
        badge: 'bg-rose-100 text-rose-900 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-200 dark:ring-rose-500/25',
        dot: 'bg-rose-500',
        text: 'text-rose-700 dark:text-rose-300',
        soft: 'bg-rose-50 dark:bg-rose-500/10',
        border: 'border-rose-300 dark:border-rose-500/40',
    },
};

const fallbackStyle: StatusStyle = statusStyles.sky;

/**
 * Warna default untuk status yang belum dipetakan.
 */
export function statusStyle(color: StatusColor | undefined): StatusStyle {
    if (!color) {
        return fallbackStyle;
    }

    return statusStyles[color] ?? fallbackStyle;
}

/**
 * Kunci warna untuk sebuah status.
 */
export function statusColor(status: BookStatus | StatusColor): StatusColor {
    switch (status) {
        case 'reading':
            return 'sky';
        case 'finished':
            return 'emerald';
        case 'on_hold':
            return 'violet';
        case 'dropped':
            return 'rose';
        case 'want_to_read':
            return 'amber';
        default:
            return status;
    }
}
