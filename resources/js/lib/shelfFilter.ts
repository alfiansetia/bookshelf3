import type { BookStatus, ShelfBook } from '@/types';

/**
 * State filter rak yang sepenuhnya lokal di frontend.
 * `spotlight` tidak menyembunyikan apa pun: status lain hanya diredupkan.
 */
export type ShelfQuery = {
    search: string;
    spotlight: BookStatus | null;
    language: string | null;
    sort: string;
    favorite: boolean;
};

export const defaultShelfQuery: ShelfQuery = {
    search: '',
    spotlight: null,
    language: null,
    sort: 'recent',
    favorite: false,
};

/**
 * Saring + urutkan kartu rak di frontend. Cerminan dari scope
 * search/sorted di App\Models\Book, tapi tanpa roundtrip server.
 * Favorit tidak menyembunyikan: ia hanya meredupkan via spotlight.
 */
export function filterShelfBooks(
    books: ShelfBook[],
    query: ShelfQuery,
): ShelfBook[] {
    const term = query.search.trim().toLowerCase();
    const needsFilter =
        term !== '' || query.language !== null;

    const filtered = needsFilter
        ? books.filter(
                (book) =>
                    (query.language === null ||
                        book.language === query.language) &&
                    (term === '' ||
                        book.title.toLowerCase().includes(term) ||
                        book.author.toLowerCase().includes(term)),
            )
        : books;

    const sorted = [...filtered];

    switch (query.sort) {
        case 'title':
            sorted.sort((a, b) => a.title.localeCompare(b.title, 'id'));
            break;
        case 'author':
            sorted.sort((a, b) => a.author.localeCompare(b.author, 'id'));
            break;
        case 'rating':
            sorted.sort((a, b) => (b.rating ?? -1) - (a.rating ?? -1));
            break;
        case 'year':
            sorted.sort(
                (a, b) => (b.published_year ?? -1) - (a.published_year ?? -1),
            );
            break;
        case 'oldest':
            sorted.sort((a, b) =>
                (a.created_at ?? '').localeCompare(b.created_at ?? ''),
            );
            break;
        case 'finished':
            sorted.sort(
                (a, b) =>
                    (b.finished_at ?? '').localeCompare(a.finished_at ?? '') ||
                    (b.created_at ?? '').localeCompare(a.created_at ?? ''),
            );
            break;
        default:
            sorted.sort((a, b) =>
                (b.created_at ?? '').localeCompare(a.created_at ?? ''),
            );
            break;
    }

    return sorted;
}

/**
 * Buku yang bisa diredupkan: status untuk spotlight status,
 * flag favorit untuk spotlight favorit.
 */
type SpotlightBook = Pick<ShelfBook, 'status' | 'is_favorite'>;

/**
 * Kelas redup untuk buku yang tidak kena spotlight status maupun favorit.
 * Layout tidak berubah, hanya opacity + saturasi yang turun.
 * Dibuat cukup terang supaya tetap terbaca di atas tema gelap.
 */
export function spotlightDim(
    book: SpotlightBook,
    spotlight: BookStatus | null,
    favoriteSpotlight = false,
): string {
    if (spotlight !== null && book.status !== spotlight) {
        return 'opacity-40 saturate-50';
    }

    if (favoriteSpotlight && !book.is_favorite) {
        return 'opacity-40 saturate-50';
    }

    return '';
}

/**
 * Kelas penegas untuk buku yang kena spotlight: ring aksen supaya
 * jelas beda dari buku normal maupun yang diredupkan.
 */
export function spotlightActive(
    book: SpotlightBook,
    spotlight: BookStatus | null,
    favoriteSpotlight = false,
): string {
    if (spotlight !== null && book.status === spotlight) {
        return 'ring-2 ring-shelf-accent/70';
    }

    if (favoriteSpotlight && book.is_favorite) {
        return 'ring-2 ring-shelf-accent/70';
    }

    return '';
}
