<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart, Star } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
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
</script>

<template>
    <div class="space-y-14">
        <section v-for="row in props.rows" :key="row.status">
            <header class="mb-5 flex items-center gap-3">
                <span
                    class="flex size-8 items-center justify-center rounded-md border border-shelf-accent/40 bg-shelf-surface-2 text-shelf-accent shadow-[0_0_18px_-5px_var(--shelf-glow)]"
                >
                    <component :is="iconByName(row.icon)" class="size-4" />
                </span>

                <h3
                    class="bg-gradient-to-r from-shelf-ink via-shelf-accent to-shelf-glow bg-clip-text text-sm font-semibold tracking-[0.18em] text-transparent uppercase"
                >
                    {{ row.label }}
                </h3>

                <span
                    class="rounded-full border border-shelf-accent/30 px-2 py-0.5 font-mono text-[10px] text-shelf-accent"
                >
                    {{ row.total }}
                </span>

                <span
                    class="h-px flex-1 bg-gradient-to-r from-shelf-accent/60 to-transparent"
                    aria-hidden="true"
                />
            </header>

            <!-- Desktop: kisi kotak seperti panel digital. -->
            <div
                class="hidden gap-3 sm:grid sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
            >
                <component
                    :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                    v-for="book in row.books"
                    :key="book.id"
                    :href="shelfBookHref(book, props.owner) ?? undefined"
                    class="group relative block aspect-3/4 overflow-hidden rounded-shelf border border-shelf-accent/25 bg-shelf-surface-2 transition duration-300 ease-out hover:-translate-y-1 hover:border-shelf-accent/70 hover:shadow-[0_0_32px_-6px_var(--shelf-glow)]"
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

            <!-- Mobile: kartu tersusun ke bawah. -->
            <ul class="space-y-2 sm:hidden">
                <li v-for="book in row.books" :key="book.id">
                    <component
                        :is="shelfBookHref(book, props.owner) ? Link : 'div'"
                        :href="shelfBookHref(book, props.owner) ?? undefined"
                        class="group flex items-center gap-3 rounded-shelf border border-shelf-accent/20 bg-shelf-surface-2 p-2 transition-colors hover:border-shelf-accent/60"
                    >
                        <BookCover
                            :book="book"
                            size="xs"
                            :spine="false"
                            class="rounded-[3px]"
                        />

                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-[13px] font-semibold text-shelf-ink"
                            >
                                {{ book.title }}
                            </span>
                            <span
                                class="block truncate font-mono text-[10px] text-shelf-accent"
                            >
                                {{ book.author }}
                            </span>
                        </span>

                        <span
                            v-if="book.rating"
                            class="flex shrink-0 items-center gap-1 font-mono text-[10px] text-shelf-glow"
                        >
                            <Star class="size-3 fill-current" />
                            {{ book.rating }}
                        </span>
                    </component>
                </li>
            </ul>

            <div class="relative mt-4" aria-hidden="true">
                <div class="shelf-plank h-1.5 rounded-full" />
                <div
                    class="absolute inset-x-0 top-1/2 h-8 -translate-y-1/2 bg-shelf-glow/25 blur-2xl"
                />
            </div>
        </section>
    </div>
</template>
