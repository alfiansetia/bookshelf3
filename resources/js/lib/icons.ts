import {
    BookCheck,
    BookOpen,
    BookX,
    Bookmark,
    Languages,
    Pause,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import type { BookStatus } from '@/types';

/**
 * Peta nama ikon yang dikirim backend (BookStatus::icon) ke komponen Lucide.
 */
const icons: Record<string, LucideIcon> = {
    Bookmark,
    BookOpen,
    BookCheck,
    BookX,
    Pause,
    Languages,
};

/**
 * Ambil komponen ikon berdasarkan namanya.
 */
export function iconByName(name: string | undefined | null): LucideIcon {
    if (!name) {
        return BookOpen;
    }

    return icons[name] ?? BookOpen;
}

/**
 * Ikon default untuk setiap status baca.
 */
export const statusIcons: Record<BookStatus, LucideIcon> = {
    want_to_read: Bookmark,
    reading: BookOpen,
    finished: BookCheck,
    on_hold: Pause,
    dropped: BookX,
};
