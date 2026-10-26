<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { formatDate } from '@/lib/format';
import { spotlightActive, spotlightDim } from '@/lib/shelfFilter';
import { shelfBookHref } from '@/lib/shelfLinks';
import { statusColor, statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import type { BookStatus, ShelfBook } from '@/types';

const props = withDefaults(
    defineProps<{
        books: ShelfBook[];
        spotlight?: BookStatus | null;
        favoriteSpotlight?: boolean;
        owner?: boolean;
    }>(),
    {
        spotlight: null,
        favoriteSpotlight: false,
        owner: false,
    },
);

/**
 * Cap waktu tiap buku: tanggal selesai kalau ada, kalau tidak
 * tanggal ditambahkan. Urutan `books` tidak diubah.
 */
function entryDate(book: ShelfBook): string | null {
    if (book.finished_at) {
        const done = formatDate(book.finished_at);

        return done ? `Selesai ${done}` : null;
    }

    const added = formatDate(book.created_at);

    return added ? `Ditambahkan ${added}` : null;
}
</script>

<template>
    <!--
        Satu garis vertikal untuk semua ukuran layar: tiap buku
        jadi titik berwarna status. Daftar tetap datar mengikuti
        urutan `books`, tanpa pengelompokan ulang.
    -->
    <ol class="relative space-y-3 pl-8">
        <span
            class="absolute top-2 bottom-2 left-[9px] w-px bg-shelf-plank"
            aria-hidden="true"
        />

        <li
            v-for="book in props.books"
            :key="book.id"
            class="relative"
        >
            <span
                :class="
                    cn(
                        'absolute top-5 -left-8 size-5 rounded-full ring-4 ring-shelf-surface',
                        statusStyle(statusColor(book.status)).dot,
                    )
                "
                aria-hidden="true"
            />

            <component
                :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                :href="shelfBookHref(book, props.owner) ?? undefined"
                :class="[
                    'group flex min-h-[4.5rem] items-center gap-3 rounded-shelf border border-black/10 bg-shelf-surface-2 p-2.5 pr-3 shadow-[0_12px_20px_-16px_rgba(20,60,45,0.7)] transition duration-300 ease-out hover:-translate-y-0.5 dark:border-white/10',
                    spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                    spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                ]"
            >
                <BookCover
                    :book="book"
                    size="xs"
                    :spine="false"
                    class="shrink-0 ring-1 ring-black/5 dark:ring-white/10"
                />

                <span class="min-w-0 flex-1">
                    <span
                        v-if="entryDate(book)"
                        class="block truncate font-mono text-[10px] tracking-[0.14em] text-shelf-accent uppercase tabular-nums"
                    >
                        {{ entryDate(book) }}
                    </span>
                    <span
                        class="mt-0.5 block truncate text-sm font-semibold text-shelf-ink transition-colors group-hover:text-shelf-accent"
                        :title="book.title"
                    >
                        {{ book.title }}
                    </span>
                    <span class="mt-0.5 block truncate text-[11px] text-shelf-muted">
                        {{ book.author }}
                        <template v-if="book.published_year">
                            · {{ book.published_year }}
                        </template>
                    </span>
                    <span class="mt-1 flex items-center gap-2">
                        <StarRating
                            v-if="book.rating"
                            :value="book.rating"
                            size="sm"
                        />
                        <span v-else class="text-[11px] text-shelf-muted italic">
                            Belum dinilai
                        </span>
                        <span
                            class="truncate text-[10px] tracking-wide text-shelf-muted uppercase"
                        >
                            {{ book.status_label }}
                        </span>
                    </span>
                </span>

                <Heart
                    v-if="book.is_favorite"
                    class="size-3.5 shrink-0 fill-current text-shelf-accent"
                    aria-label="Buku favorit"
                />
            </component>
        </li>
    </ol>
</template>
