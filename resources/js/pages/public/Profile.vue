<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BookOpen, SearchX } from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ProfileHero from '@/components/bookshelf/ProfileHero.vue';
import ShelfFilterBar from '@/components/bookshelf/ShelfFilterBar.vue';
import ShelfRenderer from '@/components/bookshelf/shelves/ShelfRenderer.vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import { create } from '@/routes/books';
import { show as shelfShow } from '@/routes/shelf';
import type {
    LanguageSummary,
    ShelfFilters,
    ShelfProfile,
    ShelfRow,
    ShelfSummary,
    SortOption,
    StatusOption,
} from '@/types';

const props = defineProps<{
    profile: ShelfProfile;
    stats: ShelfSummary;
    rows: ShelfRow[];
    statusCounts: Record<string, number>;
    languages: LanguageSummary[];
    statuses: StatusOption[];
    sortOptions: SortOption[];
    filters: ShelfFilters;
    isOwner: boolean;
    limit: number;
    filteredTotal: number;
    canonical: string;
}>();

const shelfUrl = computed(() => shelfShow.url(props.profile.username));

const hasBooks = computed(() => props.stats.total > 0);

const hasActiveFilters = computed(
    () =>
        Boolean(props.filters.search) ||
        Boolean(props.filters.status) ||
        Boolean(props.filters.language) ||
        props.filters.favorite,
);

const isTruncated = computed(() => props.filteredTotal > props.limit);

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
            <ShelfFilterBar
                :filters="props.filters"
                :statuses="props.statuses"
                :languages="props.languages"
                :sort-options="props.sortOptions"
                :status-counts="props.statusCounts"
                :url="shelfUrl"
                :total="props.filteredTotal"
            />
        </template>

        <ShelfRenderer
            v-if="props.rows.length > 0"
            :theme="props.profile.theme"
            :rows="props.rows"
            :owner="props.isOwner"
        />

        <EmptyState
            v-else-if="hasActiveFilters"
            title="Tidak ada buku yang cocok"
            description="Coba ubah filter atau kata kunci pencarianmu."
            :icon="SearchX"
        >
            <Button as-child variant="outline">
                <Link :href="shelfUrl">Bersihkan filter</Link>
            </Button>
        </EmptyState>

        <EmptyState
            v-else-if="!hasBooks"
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

        <p
            v-if="isTruncated"
            class="rounded-2xl border border-border/70 bg-card p-4 text-center text-sm text-muted-foreground"
        >
            Menampilkan {{ formatNumber(props.limit) }} dari
            {{ formatNumber(props.filteredTotal) }} buku. Gunakan filter di atas
            untuk mempersempit pilihan.
        </p>
    </div>
</template>
