<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { bookColor } from '@/lib/bookColors';
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
 * Tumpukan berurutan, 4 buku per nampan. Bukan pengelompokan
 * status: urutan `books` yang sudah difilter dipertahankan.
 */
const stacks = computed<ShelfBook[][]>(() => {
    const perStack = 4;
    const result: ShelfBook[][] = [];

    for (let start = 0; start < props.books.length; start += perStack) {
        result.push(props.books.slice(start, start + perStack));
    }

    return result;
});

/** Geser horizontal tiap lapis supaya tumpukan terlihat manual. */
const offsets = ['0px', '8px', '4px', '12px'];

/**
 * Nomor nampan dua digit ala label meja.
 */
function stackNumber(index: number): string {
    return String(index + 1).padStart(2, '0');
}
</script>

<template>
    <!--
        Satu kolom nampan di ponsel, dua kolom nampan di desktop.
        Sengaja tanpa shelf-plank: alasnya nampan meja penuh,
        bukan papan rak seperti tema kayu.
    -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-5">
        <div
            v-for="(stack, stackIndex) in stacks"
            :key="stackIndex"
            class="stack-tray rounded-shelf p-2.5 sm:p-3"
        >
            <p
                class="mb-2 flex items-center justify-between font-mono text-[10px] tracking-[0.18em] text-shelf-muted uppercase tabular-nums"
            >
                <span>Tumpukan {{ stackNumber(stackIndex) }}</span>
                <span>{{ stack.length }} buku</span>
            </p>

            <div class="flex flex-col items-stretch gap-2.5">
                <component
                    :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                    v-for="(book, index) in stack"
                    :key="book.id"
                    :href="shelfBookHref(book, props.owner) ?? undefined"
                    :class="[
                        'group relative block overflow-hidden rounded-shelf border border-black/10 bg-shelf-surface shadow-[0_14px_22px_-16px_rgba(60,40,20,0.8)] transition duration-300 ease-out hover:-translate-y-0.5 dark:border-white/10',
                        spotlightDim(book, props.spotlight, props.favoriteSpotlight),
                        spotlightActive(book, props.spotlight, props.favoriteSpotlight),
                    ]"
                    :style="{ marginLeft: offsets[index % offsets.length] }"
                >
                    <span class="flex min-h-[4.75rem] items-center gap-3 py-2 pr-3 pl-0">
                        <span
                            class="w-2.5 shrink-0 self-stretch"
                            :style="{ backgroundColor: bookColor(book) }"
                            aria-hidden="true"
                        />

                        <BookCover
                            :book="book"
                            size="xs"
                            :spine="false"
                            class="shrink-0 ring-1 ring-black/5 dark:ring-white/10"
                        />

                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-sm font-semibold text-shelf-ink transition-colors group-hover:text-shelf-accent"
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
                    </span>

                    <span
                        class="stack-pages block h-2 border-t border-black/10 dark:border-white/10"
                        aria-hidden="true"
                    />
                </component>
            </div>
        </div>
    </div>
</template>
