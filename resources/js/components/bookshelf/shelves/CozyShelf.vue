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

/** Sudut kemiringan kartu, biar terasa ditempel manual di papan. */
const tilts = ['-1.4deg', '0.9deg', '-0.6deg', '1.2deg'];
</script>

<template>
    <div class="space-y-12">
        <section v-for="row in props.rows" :key="row.status">
            <header class="mb-6 flex flex-wrap items-center gap-3">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-shelf-accent-soft px-3 py-1.5 text-sm font-semibold text-shelf-ink"
                >
                    <component :is="iconByName(row.icon)" class="size-4" />
                    {{ row.label }}
                </span>
                <span
                    class="rounded-full bg-shelf-surface-2 px-2.5 py-1 text-xs font-medium text-shelf-muted"
                >
                    {{ row.total }} buku
                </span>
                <span
                    class="h-px flex-1 bg-gradient-to-r from-shelf-plank to-transparent"
                    aria-hidden="true"
                />
            </header>

            <!-- Mobile satu kolom ke bawah, desktop jadi papan tempel beberapa kolom. -->
            <div class="columns-1 gap-5 sm:columns-2 xl:columns-3">
                <div
                    v-for="(book, index) in row.books"
                    :key="book.id"
                    class="mb-5 break-inside-avoid"
                >
                    <component
                        :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                        :href="shelfBookHref(book, props.owner) ?? undefined"
                        class="group relative block rotate-[var(--card-tilt)] rounded-shelf border border-black/5 bg-shelf-surface-2 p-2.5 pb-3 shadow-[0_18px_30px_-24px_rgba(90,50,30,0.9)] transition-transform duration-300 ease-out hover:-translate-y-1.5 hover:rotate-0 dark:border-white/10"
                        :style="{ '--card-tilt': tilts[index % tilts.length] }"
                    >
                        <span
                            class="absolute -top-2 left-1/2 h-4 w-14 -translate-x-1/2 -rotate-3 rounded-[2px] bg-shelf-accent-soft/80 shadow-sm"
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

                        <span class="mt-3 block px-1">
                            <span
                                class="block font-display text-base leading-snug break-words text-shelf-ink"
                                :title="book.title"
                            >
                                {{ book.title }}
                            </span>
                            <span
                                class="mt-0.5 block truncate text-[11px] text-shelf-muted"
                            >
                                {{ book.author }}
                            </span>
                            <span
                                class="mt-2 flex items-center justify-between gap-2"
                            >
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
        </section>
    </div>
</template>
