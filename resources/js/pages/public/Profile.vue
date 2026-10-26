<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useIntersectionObserver } from '@vueuse/core';
import { BookOpen, Heart, SearchX } from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ProfileHero from '@/components/bookshelf/ProfileHero.vue';
import ShelfSpotlightBar from '@/components/bookshelf/ShelfSpotlightBar.vue';
import ShelfRenderer from '@/components/bookshelf/shelves/ShelfRenderer.vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import {
    defaultShelfQuery,
    filterShelfBooks,
} from '@/lib/shelfFilter';
import { create } from '@/routes/books';
import { show as shelfShow } from '@/routes/shelf';
import type {
    BookStatus,
    LanguageSummary,
    ShelfBook,
    ShelfProfile,
    ShelfSummary,
    SortOption,
    StatusOption,
} from '@/types';

const props = defineProps<{
    profile: ShelfProfile;
    stats: ShelfSummary;
    books: ShelfBook[];
    statusCounts: Record<string, number>;
    languages: LanguageSummary[];
    statuses: StatusOption[];
    sortOptions: SortOption[];
    isOwner: boolean;
    canonical: string;
}>();

const shelfUrl = computed(() => shelfShow.url(props.profile.username));

const hasBooks = computed(() => props.stats.total > 0);

/**
 * Seluruh state filter lokal di frontend: tanpa reload,
 * 100 buku tersaring dalam < 1ms.
 */
const search = ref(defaultShelfQuery.search);
const spotlight = ref<BookStatus | null>(defaultShelfQuery.spotlight);
const language = ref<string | null>(defaultShelfQuery.language);
const sort = ref(defaultShelfQuery.sort);
const favorite = ref(defaultShelfQuery.favorite);

const visibleBooks = computed(() =>
    filterShelfBooks(props.books, {
        search: search.value,
        spotlight: spotlight.value,
        language: language.value,
        sort: sort.value,
        favorite: favorite.value,
    }),
);

/**
 * Render chunking: DOM hanya menampung 24 kartu pertama, sisanya
 * ditambah otomatis saat sentinel terlihat. Data sudah di FE,
 * jadi tanpa request server sama sekali.
 */
const RENDER_CHUNK = 24;
const renderLimit = ref(RENDER_CHUNK);

const renderedBooks = computed(() =>
    visibleBooks.value.slice(0, renderLimit.value),
);

const favoriteCount = computed(
    () => visibleBooks.value.filter((book) => book.is_favorite).length,
);

/**
 * Rak tampil selama ada kartu ter-render, kecuali mode favorit
 * yang tidak punya satupun favorit: tampilkan pesan khusus.
 */
const showNoFavorite = computed(
    () =>
        hasBooks.value &&
        renderedBooks.value.length > 0 &&
        favorite.value &&
        favoriteCount.value === 0,
);

const showShelf = computed(
    () => renderedBooks.value.length > 0 && !showNoFavorite.value,
);

const showNoMatch = computed(
    () => hasBooks.value && renderedBooks.value.length === 0,
);

const hasMore = computed(
    () => renderedBooks.value.length < visibleBooks.value.length,
);

function loadMore(): void {
    if (!hasMore.value) {
        return;
    }

    renderLimit.value += RENDER_CHUNK;
}

watch([search, spotlight, language, sort, favorite], () => {
    renderLimit.value = RENDER_CHUNK;
});

const sentinel = ref<HTMLDivElement | null>(null);

useIntersectionObserver(
    sentinel,
    ([entry]) => {
        if (entry?.isIntersecting) {
            loadMore();
        }
    },
    { rootMargin: '400px' },
);

const hasActiveFilters = computed(
    () =>
        search.value.trim() !== '' ||
        spotlight.value !== null ||
        language.value !== null ||
        favorite.value ||
        sort.value !== 'recent',
);

function resetFilters(): void {
    search.value = defaultShelfQuery.search;
    spotlight.value = defaultShelfQuery.spotlight;
    language.value = defaultShelfQuery.language;
    sort.value = defaultShelfQuery.sort;
    favorite.value = defaultShelfQuery.favorite;
}

const pageTitle = computed(() => `Rak buku ${props.profile.name}`);

const description = computed(
    () =>
        props.profile.tagline ??
        `Koleksi buku yang sudah dibaca ${props.profile.name}.`,
);

const structuredData = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'ProfilePage',
        url: props.canonical,
        inLanguage: 'id-ID',
        mainEntity: {
            '@type': 'Person',
            name: props.profile.name,
            alternateName: `@${props.profile.username}`,
            url: props.canonical,
            description: props.profile.bio ?? props.profile.tagline ?? undefined,
            image: props.profile.avatar_url ?? undefined,
        },
    },
]);
</script>

<template>
    <SeoHead
        :title="pageTitle"
        :description="description"
        :canonical="props.canonical"
        :image="props.profile.avatar_url"
        type="profile"
    />
    <JsonLd :data="structuredData" />

    <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-8 sm:px-6 sm:py-10">
        <ProfileHero
            :profile="props.profile"
            :stats="props.stats"
            :is-owner="props.isOwner"
            :shelf-url="shelfUrl"
        />

        <template v-if="hasBooks">
            <ShelfSpotlightBar
                :search="search"
                :spotlight="spotlight"
                :language="language"
                :sort="sort"
                :favorite="favorite"
                :statuses="props.statuses"
                :languages="props.languages"
                :sort-options="props.sortOptions"
                :status-counts="props.statusCounts"
                :shown="renderedBooks.length"
                :total="visibleBooks.length"
                @update:search="search = $event"
                @update:spotlight="spotlight = $event"
                @update:language="language = $event"
                @update:sort="sort = $event"
                @update:favorite="favorite = $event"
                @reset="resetFilters"
            />
        </template>

        <ShelfRenderer
            v-if="showShelf"
            :theme="props.profile.theme"
            :books="renderedBooks"
            :spotlight="spotlight"
            :favorite-spotlight="favorite"
            :owner="props.isOwner"
        />

        <div
            v-if="showShelf && hasMore"
            ref="sentinel"
            class="flex justify-center pt-2"
        >
            <Button type="button" variant="outline" @click="loadMore">
                Muat lebih banyak ({{ visibleBooks.length - renderedBooks.length }} lagi)
            </Button>
        </div>

        <EmptyState
            v-if="showNoFavorite"
            title="Belum ada buku favorit"
            description="Ketuk ikon hati pada buku untuk menandainya sebagai favorit."
            :icon="Heart"
        >
            <Button type="button" variant="outline" @click="resetFilters">
                Tampilkan semua buku
            </Button>
        </EmptyState>

        <EmptyState
            v-if="showNoMatch"
            title="Tidak ada buku yang cocok"
            description="Coba ubah filter atau kata kunci pencarianmu."
            :icon="SearchX"
        >
            <Button type="button" variant="outline" @click="resetFilters">
                Bersihkan filter
            </Button>
        </EmptyState>

        <EmptyState
            v-if="!hasBooks"
            title="Rak ini masih kosong"
            :description="
                props.isOwner
                    ? 'Tambahkan buku pertamamu supaya rak ini hidup.'
                    : `${props.profile.name} belum menambahkan buku apa pun.`
            "
            :icon="BookOpen"
        >
            <Button v-if="props.isOwner" as-child>
                <Link :href="create()">Tambah buku</Link>
            </Button>
        </EmptyState>
    </div>
</template>
