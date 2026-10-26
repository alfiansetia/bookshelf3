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

/** Sudut kemiringan kartu, biar terasa ditempel manual di papan. */
const tilts = ['-1.4deg', '0.9deg', '-0.6deg', '1.2deg'];
</script>

<template>
    <!-- Mobile dua kolom berdampingan, tablet ke atas tetap papan tempel beberapa kolom. -->
    <div class="columns-2 gap-3 sm:columns-2 sm:gap-5 xl:columns-3">
        <div
            v-for="(book, index) in props.books"
            :key="book.id"
            class="mb-3 break-inside-avoid sm:mb-5"
        >
            <component
                :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                :href="shelfBookHref(book, props.owner) ?? undefined"
                :class="[
                    'group relative block rotate-[var(--card-tilt)] rounded-shelf border border-black/5 bg-shelf-surface-2 p-2 pb-2.5 shadow-[0_18px_30px_-24px_rgba(90,50,30,0.9)] transition duration-300 ease-out hover:-translate-y-1.5 hover:rotate-0 sm:p-2.5 sm:pb-3 dark:border-white/10',
                    spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                    spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                ]"
                :style="{ '--card-tilt': tilts[index % tilts.length] }"
            >
                <span
                    class="absolute -top-2 left-1/2 h-4 w-10 -translate-x-1/2 -rotate-3 rounded-[2px] bg-shelf-accent-soft/80 shadow-sm sm:w-14"
                    aria-hidden="true"
                />

                <span class="relative block">
                    <BookCover
                        :book="book"
                        size="lg"
                        :spine="false"
                        class="mx-auto"
                    />

                    <span
                        v-if="book.is_favorite"
                        class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-full bg-shelf-accent text-white shadow-md"
                        aria-label="Buku favorit"
                    >
                        <Heart class="size-3 fill-current" />
                    </span>
                </span>

                <span class="mt-2 block px-0.5 sm:mt-3 sm:px-1">
                    <span
                        class="block font-display text-sm leading-snug break-words text-shelf-ink sm:text-base"
                        :title="book.title"
                    >
                        {{ book.title }}
                    </span>
                    <span class="mt-0.5 block truncate text-[11px] text-shelf-muted">
                        {{ book.author }}
                    </span>
                    <span class="mt-2 flex items-center justify-between gap-2">
                        <StarRating
                            v-if="book.rating"
                            :value="book.rating"
                            size="sm"
                        />
                        <span v-else class="text-[11px] text-shelf-muted italic">
                            Belum dinilai
                        </span>
                        <span
                            v-if="book.published_year"
                            class="font-mono text-[10px] text-shelf-muted"
                        >
                            {{ book.published_year }}
                        </span>
                    </span>
                </span>
            </component>
        </div>
    </div>
</template>
