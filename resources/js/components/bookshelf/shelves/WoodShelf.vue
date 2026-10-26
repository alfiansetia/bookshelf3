<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { bookColor, bookColorInk } from '@/lib/bookColors';
import { spotlightActive, spotlightDim } from '@/lib/shelfFilter';
import { shelfBookHref } from '@/lib/shelfLinks';
import type { BookStatus, ShelfBook } from '@/types';

/**
 * Punggung buku: warna milik bukunya sendiri, bukan warna rak.
 */
type Spine = {
    book: ShelfBook;
    color: string;
    ink: string;
    height: string;
};

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

/** Sama seperti desktop, tapi muat di layar kecil. */
const isDesktop = useMediaQuery('(min-width: 768px)');

/** Tinggi punggung berbeda-beda supaya rak terlihat seperti buku sungguhan. */
const heights = [15.5, 13.75, 16.5, 14.25, 17, 13];

/**
 * Bagi buku menjadi beberapa papan: 10 per papan di desktop,
 * 6 per papan di mobile supaya muat tanpa scroll horizontal.
 * Tinggi punggung diskala 0.68 di mobile, penuh di desktop.
 * Warna punggung ikut warna tiap buku (pilihan pemilik atau acak stabil).
 */
const planks = computed<Spine[][]>(() => {
    const perPlank = isDesktop.value ? 10 : 6;
    const scale = isDesktop.value ? 1 : 0.68;
    const result: Spine[][] = [];

    for (let start = 0; start < props.books.length; start += perPlank) {
        result.push(
            props.books.slice(start, start + perPlank).map((book, offset) => {
                const seed = start + offset;
                const color = bookColor(book);

                return {
                    book,
                    color,
                    ink: bookColorInk(color),
                    height: `${(heights[seed % heights.length] * scale).toFixed(2)}rem`,
                };
            }),
        );
    }

    return result;
});
</script>

<template>
    <!--
        Satu tata letak untuk semua ukuran layar: buku berdiri
        berdampingan di atas papan kayu. Tanpa versi list terpisah.
    -->
    <div class="space-y-7">
        <div v-for="(plank, plankIndex) in planks" :key="plankIndex">
            <div class="flex flex-wrap items-end gap-x-2 gap-y-4">
                <component
                    :is="
                        shelfBookHref(spine.book, props.owner) ? Link : 'div'
                    "
                    v-for="spine in plank"
                    :key="spine.book.id"
                    :href="
                        shelfBookHref(spine.book, props.owner) ?? undefined
                    "
                    :class="[
                        'group relative flex w-9 shrink-0 items-center justify-center rounded-t-[4px] shadow-[0_14px_20px_-16px_rgba(0,0,0,0.75)] transition duration-300 ease-out hover:-translate-y-2 sm:w-10',
                        spotlightDim(spine.book, props.spotlight, props.favoriteSpotlight),
                        spotlightActive(spine.book, props.spotlight, props.favoriteSpotlight),
                    ]"
                    :style="{
                        height: spine.height,
                        backgroundColor: spine.color,
                        color: spine.ink,
                    }"
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
                        class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 hidden w-40 -translate-x-1/2 group-hover:block"
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
                            <p class="truncate text-[11px] text-shelf-muted">
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
</template>
