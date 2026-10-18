<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import { iconByName } from '@/lib/icons';
import { shelfBookHref } from '@/lib/shelfLinks';
import type { ShelfRow } from '@/types';

const props = withDefaults(
    defineProps<{
        rows: ShelfRow[];
        owner?: boolean;
    }>(),
    {
        owner: false,
    },
);

/**
 * Nomor urut dua digit untuk daftar indeks.
 */
function entryNumber(index: number): string {
    return String(index + 1).padStart(2, '0');
}
</script>

<template>
    <div class="space-y-12">
        <section v-for="row in props.rows" :key="row.status">
            <header class="mb-2 flex items-center gap-3">
                <component
                    :is="iconByName(row.icon)"
                    class="size-3.5 text-shelf-muted"
                />
                <h3
                    class="text-[11px] font-semibold tracking-[0.22em] text-shelf-ink uppercase"
                >
                    {{ row.label }}
                </h3>
                <span
                    class="font-mono text-[11px] text-shelf-muted tabular-nums"
                >
                    {{ row.total }}
                </span>
                <span class="h-px flex-1 bg-shelf-plank" aria-hidden="true" />
            </header>

            <!-- Desktop dua kolom, mobile satu kolom tersusun ke bawah. -->
            <ol class="grid grid-cols-1 sm:grid-cols-2 sm:gap-x-10">
                <li
                    v-for="(book, index) in row.books"
                    :key="book.id"
                    class="min-w-0 border-b border-shelf-plank/70"
                >
                    <component
                        :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                        :href="shelfBookHref(book, props.owner) ?? undefined"
                        class="group -mx-2 flex items-center gap-3.5 rounded-shelf px-2 py-3 transition-colors hover:bg-shelf-surface-2"
                    >
                        <span
                            class="w-6 shrink-0 font-mono text-[11px] text-shelf-muted tabular-nums transition-colors group-hover:text-shelf-accent"
                        >
                            {{ entryNumber(index) }}
                        </span>

                        <BookCover
                            :book="book"
                            size="xs"
                            :spine="false"
                            class="rounded-[2px] ring-1 ring-black/5 transition-transform duration-300 ease-out group-hover:-translate-y-1 dark:ring-white/10"
                        />

                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-sm font-medium text-shelf-ink transition-colors group-hover:text-shelf-accent"
                            >
                                {{ book.title }}
                            </span>
                            <span
                                class="block truncate text-[11px] text-shelf-muted"
                            >
                                {{ book.author }}
                                <template v-if="book.published_year">
                                    · {{ book.published_year }}
                                </template>
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
            </ol>
        </section>
    </div>
</template>
