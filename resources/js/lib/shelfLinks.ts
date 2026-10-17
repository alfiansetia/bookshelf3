import type { Book } from '@/types';
import { edit as editBook } from '@/routes/books';

/**
 * Tautan sebuah buku di rak: halaman edit kalau yang melihat pemilik rak,
 * atau halaman publik buku untuk pengunjung.
 */
export function shelfBookHref(book: Book, owner: boolean): string | null {
    if (owner && book.can.update) {
        return editBook(book.id).url;
    }

    return book.public_url;
}
