<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart, Star } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
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
</script>

<template>
    <div>
        <!-- Desktop: kisi kotak seperti panel digital. -->
        <div
            class="hidden gap-3 sm:grid sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
        >
            <component
                :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                v-for="book in props.books"
                :key="book.id"
                :href="shelfBookHref(book, props.owner) ?? undefined"
                :class="[
                    'group relative block aspect-3/4 overflow-hidden rounded-shelf border border-shelf-accent/25 bg-shelf-surface-2 transition duration-300 ease-out hover:-translate-y-1 hover:border-shelf-accent/70 hover:shadow-[0_0_32px_-6px_var(--shelf-glow)]',
                    spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                    spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                ]"
            >
                <BookCover
                    :book="book"
                    size="lg"
                    :spine="false"
                    class="absolute inset-0 h-full w-full rounded-none border-0"
                />

                <span
                    class="pointer-events-none absolute inset-0 bg-[repeating-linear-gradient(0deg,rgba(255,255,255,0.05)_0_1px,transparent_1px_4px)] mix-blend-screen"
                    aria-hidden="true"
                />

                <span
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-shelf-surface via-shelf-surface/80 to-transparent"
                    aria-hidden="true"
                />

                <span class="absolute inset-x-0 bottom-0 p-3">
                    <span
                        class="block truncate text-[12px] font-semibold text-shelf-ink"
                    >
                        {{ book.title }}
                    </span>
                    <span
                        class="mt-0.5 block truncate font-mono text-[10px] text-shelf-accent"
                    >
                        {{ book.author }}
                    </span>
                    <span
                        class="mt-1.5 flex items-center gap-2 font-mono text-[10px] text-shelf-muted"
                    >
                        <span
                            v-if="book.rating"
                            class="flex items-center gap-1 text-shelf-glow"
                        >
                            <Star class="size-3 fill-current" />
                            {{ book.rating }}.0
                        </span>
                        <span v-if="book.published_year">
                            {{ book.published_year }}
                        </span>
                    </span>
                </span>

                <span
                    v-if="book.is_favorite"
                    class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-full border border-shelf-glow/70 bg-shelf-surface/80 text-shelf-glow shadow-[0_0_14px_-2px_var(--shelf-glow)]"
                    aria-label="Buku favorit"
                >
                    <Heart class="size-3 fill-current" />
                </span>
            </component>
        </div>

        <!-- Mobile: kisi 2 kolom kartu mini, desktop tidak berubah. -->
        <div class="grid grid-cols-2 gap-2 sm:hidden">
            <component
                :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                v-for="book in props.books"
                :key="book.id"
                :href="shelfBookHref(book, props.owner) ?? undefined"
                :class="[
                    'group relative block aspect-3/4 overflow-hidden rounded-shelf border border-shelf-accent/25 bg-shelf-surface-2 transition duration-300 ease-out active:scale-[0.98]',
                    spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                    spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                ]"
            >
                <BookCover
                    :book="book"
                    size="md"
                    :spine="false"
                    class="absolute inset-0 h-full w-full rounded-none border-0"
                />

                <span
                    class="pointer-events-none absolute inset-0 bg-[repeating-linear-gradient(0deg,rgba(255,255,255,0.05)_0_1px,transparent_1px_4px)] mix-blend-screen"
                    aria-hidden="true"
                />

                <span
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-shelf-surface via-shelf-surface/80 to-transparent"
                    aria-hidden="true"
                />

                <span class="absolute inset-x-0 bottom-0 p-2">
                    <span
                        class="block truncate text-[11px] font-semibold text-shelf-ink"
                    >
                        {{ book.title }}
                    </span>
                    <span
                        class="mt-0.5 block truncate font-mono text-[9px] text-shelf-accent"
                    >
                        {{ book.author }}
                    </span>
                    <span
                        v-if="book.rating"
                        class="mt-1 flex items-center gap-1 font-mono text-[9px] text-shelf-glow"
                    >
                        <Star class="size-2.5 fill-current" />
                        {{ book.rating }}
                    </span>
                </span>

                <span
                    v-if="book.is_favorite"
                    class="absolute top-1.5 right-1.5 flex size-5 items-center justify-center rounded-full border border-shelf-glow/70 bg-shelf-surface/80 text-shelf-glow"
                    aria-label="Buku favorit"
                >
                    <Heart class="size-2.5 fill-current" />
                </span>
            </component>
        </div>

        <div class="relative mt-4" aria-hidden="true">
            <div class="shelf-plank h-1.5 rounded-full" />
            <div
                class="absolute inset-x-0 top-1/2 h-8 -translate-y-1/2 bg-shelf-glow/25 blur-2xl"
            />
        </div>
    </div>
</template>
