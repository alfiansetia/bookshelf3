<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { spotlightActive, spotlightDim } from '@/lib/shelfFilter';
import { shelfBookHref } from '@/lib/shelfLinks';
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
 * Nomor katalog tiga digit ala kartu perpustakaan.
 */
function catalogNumber(index: number): string {
    return String(index + 1).padStart(3, '0');
}
</script>

<template>
    <!-- Mobile satu kolom ke bawah, tablet dua kolom, desktop tiga kolom. -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
        <component
            :is="shelfBookHref(book, props.owner) ? Link : 'div'"
            v-for="(book, index) in props.books"
            :key="book.id"
            :href="shelfBookHref(book, props.owner) ?? undefined"
            :class="[
                'group relative block rounded-shelf border border-black/10 bg-shelf-surface-2 p-3 shadow-[0_14px_24px_-20px_rgba(60,50,80,0.8)] transition duration-300 ease-out hover:-translate-y-1 dark:border-white/10',
                spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                spotlightActive(book, props.spotlight, props.favoriteSpotlight),
            ]"
        >
            <!-- Lubang kartu katalog di tengah tepi atas. -->
            <span
                class="absolute -top-1.5 left-1/2 size-3 -translate-x-1/2 rounded-full bg-shelf-surface ring-1 ring-black/15 dark:ring-white/15"
                aria-hidden="true"
            />

            <span class="flex items-center justify-between gap-2">
                <span
                    class="font-mono text-[10px] tracking-widest text-shelf-muted tabular-nums"
                >
                    No. {{ catalogNumber(index) }}
                </span>
                <span
                    class="-rotate-2 rounded-[3px] border border-shelf-accent/70 px-1.5 py-0.5 text-[9px] font-bold tracking-[0.14em] text-shelf-accent uppercase"
                >
                    {{ book.status_label }}
                </span>
            </span>

            <span class="mt-2.5 flex gap-3">
                <BookCover
                    :book="book"
                    size="sm"
                    :spine="false"
                    class="shrink-0 ring-1 ring-black/5 transition-transform duration-300 ease-out group-hover:-translate-y-0.5 dark:ring-white/10"
                />

                <span class="min-w-0 flex-1">
                    <span
                        class="block truncate text-sm font-medium text-shelf-ink transition-colors group-hover:text-shelf-accent"
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
                    <span
                        class="mt-1 block text-[10px] tracking-wide text-shelf-muted uppercase"
                    >
                        {{ book.language_label }}
                    </span>

                    <span class="mt-1.5 flex items-center gap-2">
                        <StarRating
                            v-if="book.rating"
                            :value="book.rating"
                            size="sm"
                        />
                        <span
                            v-else
                            class="text-[11px] text-shelf-muted italic"
                        >
                            Belum dinilai
                        </span>
                        <Heart
                            v-if="book.is_favorite"
                            class="size-3.5 shrink-0 fill-current text-shelf-accent"
                            aria-label="Buku favorit"
                        />
                    </span>
                </span>
            </span>
        </component>
    </div>
</template>
