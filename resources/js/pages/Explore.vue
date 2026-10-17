<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Compass, Search, SearchX, Sparkles, Users } from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import SectionHeading from '@/components/bookshelf/SectionHeading.vue';
import ShelfPagination from '@/components/bookshelf/ShelfPagination.vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';
import { explore, register } from '@/routes';
import { show as shelfShow } from '@/routes/shelf';
import type { Paginated, ShelfCard, SortOption } from '@/types';

const props = defineProps<{
    shelves: Paginated<ShelfCard>;
    filters: { search: string | null; sort: string };
    sortOptions: SortOption[];
    canonical: string;
    numbers: { readers: number; books: number; finished: number };
}>();

const description =
    'Telusuri rak buku publik dari para pembaca dan temukan rekomendasi bacaan berikutnya, lengkap dengan rating dan catatan pribadinya.';

const isSearchResult = computed(() => Boolean(props.filters.search));

const structuredData = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'CollectionPage',
        name: 'Jelajah rak publik',
        url: props.canonical,
        description,
        inLanguage: 'id-ID',
    },
]);

const search = ref(props.filters.search ?? '');

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    },
);

function visit(overrides: Record<string, string | null> = {}): void {
    const query: Record<string, string> = {
        sort: props.filters.sort,
    };

    const merged = { ...props.filters, ...overrides };

    if (merged.search) {
        query.search = merged.search;
    }

    if (merged.sort) {
        query.sort = merged.sort;
    }

    router.get(explore.url(), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const searchDebounced = useDebounceFn(
    () => visit({ search: search.value.trim() || null }),
    350,
);

function changeSort(value: string): void {
    visit({ sort: value });
}
</script>

<template>
    <SeoHead
        title="Jelajah rak publik"
        :description="description"
        :canonical="props.canonical"
        :noindex="isSearchResult"
    />
    <JsonLd :data="structuredData" />

    <div class="mx-auto w-full max-w-6xl space-y-8 px-4 py-10 sm:px-6 sm:py-14">
        <section class="space-y-6 text-center">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
            >
                <Compass class="size-3.5" />
                {{ formatNumber(props.numbers.readers) }} rak publik
            </span>

            <h1
                class="font-display text-4xl leading-tight text-balance text-foreground sm:text-5xl"
            >
                Temukan rak yang menarik untuk ditelusuri
            </h1>

            <p
                class="mx-auto max-w-2xl text-base leading-relaxed text-muted-foreground"
            >
                Lihat buku apa saja yang dibaca orang lain, lengkap dengan
                rating dan catatan pribadinya. Klik rak mana saja untuk membuka
                koleksinya.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-6 pt-2">
                <span class="text-sm text-muted-foreground">
                    <span class="font-display text-xl text-foreground">
                        {{ formatNumber(props.numbers.books) }}
                    </span>
                    buku tercatat
                </span>
                <span class="text-sm text-muted-foreground">
                    <span class="font-display text-xl text-foreground">
                        {{ formatNumber(props.numbers.finished) }}
                    </span>
                    sudah tamat
                </span>
            </div>
        </section>

        <section
            class="flex flex-col gap-3 rounded-2xl border border-border/70 bg-card p-4 shadow-xs sm:flex-row sm:items-end"
        >
            <div class="grid flex-1 gap-2">
                <Label for="search">Cari rak</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="search"
                        v-model="search"
                        type="search"
                        class="pl-9"
                        placeholder="Cari nama pembaca, username, atau tagline..."
                        @input="searchDebounced"
                    />
                </div>
            </div>

            <div class="grid gap-2 sm:w-56">
                <Label for="sort">Urutkan</Label>
                <Select
                    :model-value="props.filters.sort"
                    @update:model-value="changeSort($event as string)"
                >
                    <SelectTrigger id="sort" class="w-full">
                        <SelectValue placeholder="Urutkan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.sortOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </section>

        <template v-if="props.shelves.data.length > 0">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="shelf in props.shelves.data"
                    :key="shelf.username"
                    class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs transition-shadow hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 font-display text-lg text-primary"
                        >
                            <img
                                v-if="shelf.avatar_url"
                                :src="shelf.avatar_url"
                                :alt="shelf.name"
                                class="size-full object-cover"
                            />
                            <template v-else>
                                {{ shelf.name.charAt(0).toUpperCase() }}
                            </template>
                        </span>

                        <div class="min-w-0">
                            <p class="truncate font-medium text-foreground">
                                {{ shelf.name }}
                            </p>
                            <p class="truncate text-xs text-primary">
                                /{{ shelf.username }}
                            </p>
                        </div>
                    </div>

                    <div
                        :data-shelf-theme="shelf.theme"
                        :class="
                            cn('space-y-2', shelf.theme === 'neon' && 'dark')
                        "
                    >
                        <div class="flex items-end gap-1.5">
                            <div
                                v-for="index in 6"
                                :key="index"
                                class="aspect-2/3 flex-1 rounded-[3px] shadow-sm"
                                :style="{
                                    backgroundColor: 'var(--shelf-surface-2)',
                                    border: '1px solid var(--shelf-plank)',
                                }"
                            />
                        </div>
                        <div class="shelf-plank h-2 rounded-full" />
                    </div>

                    <p
                        class="line-clamp-2 min-h-10 text-sm text-muted-foreground"
                    >
                        {{ shelf.tagline ?? 'Rak buku pribadi.' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-muted-foreground"
                        >
                            <Sparkles class="size-3" />
                            {{ shelf.theme_label }}
                        </span>
                        <span class="text-muted-foreground">
                            {{ formatNumber(shelf.books_count) }} buku ·
                            {{ formatNumber(shelf.finished_count) }} tamat
                        </span>
                    </div>

                    <Button as-child class="mt-auto w-full">
                        <Link :href="shelfShow(shelf.username)">
                            Kunjungi rak
                        </Link>
                    </Button>
                </article>
            </div>

            <ShelfPagination :paginator="props.shelves" />
        </template>

        <EmptyState
            v-else-if="props.filters.search"
            title="Rak tidak ditemukan"
            description="Coba kata kunci lain atau telusuri semua rak yang tersedia."
            :icon="SearchX"
        >
            <Button as-child variant="outline">
                <Link :href="explore()">Tampilkan semua rak</Link>
            </Button>
        </EmptyState>

        <EmptyState
            v-else
            title="Belum ada rak publik"
            description="Jadilah yang pertama membagikan rak bukumu di sini."
            :icon="Users"
        >
            <Button as-child>
                <Link :href="register()">
                    <Sparkles class="size-4" />
                    Buat rak pertamaku
                </Link>
            </Button>
        </EmptyState>

        <SectionHeading
            v-if="props.shelves.data.length > 0"
            align="center"
            title="Punya rak sendiri?"
            description="Buat rak, pilih gayanya, lalu bagikan tautannya ke teman-temanmu."
        >
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                <Button as-child>
                    <Link :href="register()">Mulai gratis</Link>
                </Button>
            </div>
        </SectionHeading>
    </div>
</template>
