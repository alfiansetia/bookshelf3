<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { iconByName } from '@/lib/icons';
import { shelfBookHref } from '@/lib/shelfLinks';
import type { Book, ShelfRow } from '@/types';

/**
 * Punggung buku lengkap dengan warna dan tingginya.
 */
type Spine = {
    book: Book;
    skin: string;
    height: string;
};

const props = withDefaults(
    defineProps<{
        rows: ShelfRow[];
        owner?: boolean;
    }>(),
    {
        owner: false,
    },
);

/** Warna punggung berselang-seling, semuanya dari token tema kayu. */
const skins = [
    'bg-shelf-plank text-shelf-surface',
    'bg-shelf-plank-edge text-shelf-surface',
    'bg-shelf-accent text-white',
    'bg-shelf-surface-2 text-shelf-ink',
    'bg-shelf-accent-soft text-shelf-ink',
];

/** Tinggi punggung berbeda-beda supaya rak terlihat seperti buku sungguhan. */
const heights = [
    '15.5rem',
    '13.75rem',
    '16.5rem',
    '14.25rem',
    '17rem',
    '13rem',
];

/** Jumlah buku dalam satu papan kayu. */
const booksPerPlank = 10;

/**
 * Bagi buku menjadi beberapa papan, lalu tentukan warna dan tinggi tiap punggung.
 */
function toPlanks(books: Book[]): Spine[][] {
    const planks: Spine[][] = [];

    for (let start = 0; start < books.length; start += booksPerPlank) {
        planks.push(
            books.slice(start, start + booksPerPlank).map((book, offset) => {
                const seed = start + offset;

                return {
                    book,
                    skin: skins[seed % skins.length],
                    height: heights[seed % heights.length],
                };
            }),
        );
    }

    return planks;
}
</script>

<template>
    <div class="space-y-14">
        <section v-for="row in props.rows" :key="row.status">
            <header class="mb-6 flex items-end justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-9 items-center justify-center rounded-shelf bg-shelf-accent-soft text-shelf-accent"
                    >
                        <component :is="iconByName(row.icon)" class="size-4" />
                    </span>
                    <div>
                        <h3 class="font-display text-xl text-shelf-ink">
                            {{ row.label }}
                        </h3>
                        <p class="text-xs text-shelf-muted">
                            {{ row.total }} buku di rak ini
                        </p>
                    </div>
                </div>

                <span
                    class="hidden h-px flex-1 bg-gradient-to-r from-shelf-plank to-transparent sm:block"
                    aria-hidden="true"
                />
            </header>

            <!-- Desktop: buku berdiri berdampingan di atas papan kayu. -->
            <div class="hidden space-y-7 md:block">
                <div
                    v-for="(plank, plankIndex) in toPlanks(row.books)"
                    :key="plankIndex"
                >
                    <div class="flex flex-wrap items-end gap-x-2 gap-y-4">
                        <component
                            :is="
                                shelfBookHref(spine.book, props.owner)
                                    ? Link
                                    : 'div'
                            "
                            v-for="spine in plank"
                            :key="spine.book.id"
                            :href="
                                shelfBookHref(spine.book, props.owner) ??
                                undefined
                            "
                            :class="[
                                'group relative flex w-9 shrink-0 items-center justify-center rounded-t-[4px] shadow-[0_14px_20px_-16px_rgba(0,0,0,0.75)] transition-transform duration-300 ease-out hover:-translate-y-2 sm:w-10',
                                spine.skin,
                            ]"
                            :style="{ height: spine.height }"
                        >
                            <span
                                class="max-h-full overflow-hidden px-1 text-[10px] leading-tight font-semibold [writing-mode:vertical-rl]"
                            >
                                {{ spine.book.title }}
                            </span>

                            <span
                                v-if="spine.book.is_favorite"
                                class="absolute top-2.5 left-1/2 -translate-x-1/2"
                                aria-label="Buku favorit"
                            >
                                <Heart class="size-3 fill-current" />
                            </span>

                            <div
                                class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 w-40 -translate-x-1/2"
                                aria-hidden="true"
                            >
                                <div
                                    class="translate-y-2 rounded-shelf border border-black/10 bg-shelf-surface-2 p-3 text-left opacity-0 shadow-xl transition duration-200 ease-out group-hover:translate-y-0 group-hover:opacity-100 dark:border-white/10"
                                >
                                    <BookCover
                                        :book="spine.book"
                                        size="sm"
                                        :spine="false"
                                        class="mx-auto"
                                    />
                                    <p
                                        class="mt-2.5 line-clamp-2 text-xs leading-snug font-semibold text-shelf-ink"
                                    >
                                        {{ spine.book.title }}
                                    </p>
                                    <p
                                        class="truncate text-[11px] text-shelf-muted"
                                    >
                                        {{ spine.book.author }}
                                    </p>
                                    <StarRating
                                        v-if="spine.book.rating"
                                        :value="spine.book.rating"
                                        size="sm"
                                        class="mt-1.5"
                                    />
                                </div>
                            </div>
                        </component>
                    </div>

                    <div
                        class="shelf-plank h-3.5 rounded-t-[2px] rounded-b-[4px]"
                        aria-hidden="true"
                    />
                </div>
            </div>

            <!-- Mobile: buku tersusun ke bawah. -->
            <ul class="divide-y divide-shelf-plank md:hidden">
                <li v-for="book in row.books" :key="book.id">
                    <component
                        :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                        :href="shelfBookHref(book, props.owner) ?? undefined"
                        class="group flex items-center gap-3 py-3"
                    >
                        <BookCover :book="book" size="xs" :spine="false" />

                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-sm font-semibold text-shelf-ink group-hover:text-shelf-accent"
                            >
                                {{ book.title }}
                            </span>
                            <span
                                class="block truncate text-[11px] text-shelf-muted"
                            >
                                {{ book.author }}
                            </span>
                        </span>

                        <StarRating
                            v-if="book.rating"
                            :value="book.rating"
                            size="sm"
                            class="shrink-0"
                        />

                        <Heart
                            v-if="book.is_favorite"
                            class="size-3.5 shrink-0 fill-current text-shelf-accent"
                            aria-label="Buku favorit"
                        />
                    </component>
                </li>
            </ul>
        </section>
    </div>
</template>
