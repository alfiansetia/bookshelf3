import type { Book } from '@/types';

/**
 * Tautan sebuah buku di rak: selalu ke halaman detail publik.
 * Pemilik mengedit lewat tombol "Ubah" di dashboard / halaman detail,
 * bukan lewat klik badan buku di rak.
 */
export function shelfBookHref(
    book: Pick<Book, 'public_url'>,
    _owner?: boolean,
): string | null {
    void _owner;

    return book.public_url;
}
