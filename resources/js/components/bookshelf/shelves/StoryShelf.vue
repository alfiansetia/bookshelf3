<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Heart, Star } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import { formatNumber } from '@/lib/format';
import { shelfBookHref } from '@/lib/shelfLinks';
import { spotlightActive, spotlightDim } from '@/lib/shelfFilter';
import { useDragScroll } from '@/composables/useDragScroll';
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

/** Strip reel bisa digeser dengan drag mouse, tidak hanya panah. */
const reel = ref<HTMLDivElement | null>(null);

useDragScroll(reel);

/**
 * Geser strip reel tanpa menyimpan ref.
 */
function nudge(event: Event, direction: 1 | -1): void {
    const wrap = (event.currentTarget as HTMLElement | null)?.closest(
        '[data-reel-wrap]',
    );

    if (!wrap) {
        return;
    }

    const reel = wrap.querySelector<HTMLElement>('[data-reel]');

    if (!reel) {
        return;
    }

    reel.scrollBy({
        left: direction * Math.min(reel.clientWidth * 0.8, 340),
        behavior: 'smooth',
    });
}

/**
 * Nomor urut dua digit ala indikator story.
 */
function reelIndex(index: number): string {
    return String(index + 1).padStart(2, '0');
}
</script>

<template>
    <!--
        Satu strip reel untuk semua buku: identitasnya sama di semua
        ukuran layar. Satu kartu mengintip di tepi supaya pengunjung
        tahu stripnya bisa digeser.
    -->
    <div data-reel-wrap class="group/reel relative">
        <div
            ref="reel"
            data-reel
            class="no-scrollbar -mx-4 flex cursor-grab snap-x snap-mandatory gap-4 overflow-x-auto px-4 pt-1 pb-3 active:cursor-grabbing sm:-mx-8 sm:px-8"
        >
            <component
                :is="shelfBookHref(book, props.owner) ? Link : 'article'"
                v-for="(book, index) in props.books"
                :key="book.id"
                :href="shelfBookHref(book, props.owner) ?? undefined"
                :class="[
                    'group relative w-[74vw] max-w-[300px] shrink-0 snap-center overflow-hidden rounded-shelf border border-white/10 bg-shelf-surface-2 shadow-[0_24px_50px_-24px_rgba(0,0,0,0.9)] transition duration-300 ease-out active:scale-[0.98] sm:w-[280px] sm:hover:-translate-y-1',
                    spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                    spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                ]"
            >
                <div class="relative aspect-[9/14]">
                    <BookCover
                        :book="book"
                        size="lg"
                        :spine="false"
                        class="absolute inset-0 h-full w-full rounded-none border-0"
                    />

                    <span
                        class="pointer-events-none absolute inset-x-0 top-0 h-2/5 bg-gradient-to-b from-black/70 to-transparent"
                        aria-hidden="true"
                    />
                    <span
                        class="pointer-events-none absolute inset-x-0 bottom-0 h-3/5 bg-gradient-to-t from-black/85 via-black/45 to-transparent"
                        aria-hidden="true"
                    />

                    <span class="absolute inset-x-0 top-0 p-3">
                        <span class="mb-2 flex h-1 gap-1" aria-hidden="true">
                            <span
                                class="h-full flex-1 overflow-hidden rounded-full bg-white/25"
                            >
                                <span
                                    class="block h-full rounded-full bg-shelf-accent"
                                    :style="{
                                        width: `${((index + 1) / Math.max(props.books.length, 1)) * 100}%`,
                                    }"
                                />
                            </span>
                        </span>
                        <span class="flex items-center justify-between">
                            <span
                                class="font-mono text-[10px] tracking-widest text-white/85 tabular-nums"
                            >
                                {{ reelIndex(index) }} /
                                {{
                                    reelIndex(Math.max(props.books.length - 1, 0))
                                }}
                            </span>
                            <span
                                v-if="book.is_favorite"
                                class="flex size-6 items-center justify-center rounded-full bg-shelf-accent text-[hsl(252_28%_9%)]"
                                aria-label="Buku favorit"
                            >
                                <Heart class="size-3 fill-current" />
                            </span>
                        </span>
                    </span>

                    <span class="absolute inset-x-0 bottom-0 p-4">
                        <span
                            class="block line-clamp-2 font-display text-lg leading-snug text-white"
                        >
                            {{ book.title }}
                        </span>
                        <span class="mt-0.5 block truncate text-xs text-white/70">
                            {{ book.author }}
                        </span>
                        <span
                            class="mt-2.5 flex items-center gap-2 text-[11px] text-white/80"
                        >
                            <span
                                v-if="book.rating"
                                class="inline-flex items-center gap-1 rounded-full bg-black/45 px-2 py-0.5 font-semibold text-shelf-accent"
                            >
                                <Star class="size-3 fill-current" />
                                {{ formatNumber(book.rating) }}
                            </span>
                            <span
                                v-if="book.published_year"
                                class="font-mono text-[10px] text-white/65 tabular-nums"
                            >
                                {{ book.published_year }}
                            </span>
                            <span
                                v-if="book.language_label"
                                class="truncate font-mono text-[10px] text-white/65 uppercase"
                            >
                                · {{ book.language_label }}
                            </span>
                        </span>
                    </span>
                </div>
            </component>
        </div>

        <div
            class="pointer-events-none absolute inset-y-0 -left-2 hidden items-center opacity-0 transition-opacity duration-300 group-hover/reel:opacity-100 sm:flex"
        >
            <button
                type="button"
                class="pointer-events-auto flex size-9 items-center justify-center rounded-full border border-shelf-accent/40 bg-shelf-surface/90 text-shelf-muted shadow-lg backdrop-blur transition hover:border-shelf-accent/70 hover:text-shelf-accent focus-visible:opacity-100"
                aria-label="Geser ke kiri"
                @click="nudge($event, -1)"
            >
                <ChevronLeft class="size-4" />
            </button>
        </div>
        <div
            class="pointer-events-none absolute inset-y-0 -right-2 hidden items-center opacity-0 transition-opacity duration-300 group-hover/reel:opacity-100 sm:flex"
        >
            <button
                type="button"
                class="pointer-events-auto flex size-9 items-center justify-center rounded-full border border-shelf-accent/40 bg-shelf-surface/90 text-shelf-muted shadow-lg backdrop-blur transition hover:border-shelf-accent/70 hover:text-shelf-accent focus-visible:opacity-100"
                aria-label="Geser ke kanan"
                @click="nudge($event, 1)"
            >
                <ChevronRight class="size-4" />
            </button>
        </div>

        <p class="mt-1 text-[11px] text-shelf-muted sm:hidden">
            Geser ke samping untuk melihat {{ props.books.length }} buku.
        </p>
    </div>
</template>
