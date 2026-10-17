<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Library, Plus, SearchX } from '@lucide/vue';
import BookCard from '@/components/bookshelf/BookCard.vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import ShelfFilterBar from '@/components/bookshelf/ShelfFilterBar.vue';
import ShelfPagination from '@/components/bookshelf/ShelfPagination.vue';
import StatCard from '@/components/bookshelf/StatCard.vue';
import { Button } from '@/components/ui/button';
import { formatNumber } from '@/lib/format';
import { dashboard } from '@/routes';
import { create, index as booksIndex } from '@/routes/books';
import type {
    Book,
    LanguageOption,
    LanguageSummary,
    Paginated,
    ShelfFilters,
    ShelfSummary,
    SortOption,
    StatusOption,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Rak saya', href: booksIndex() }],
    },
});

const props = defineProps<{
    books: Paginated<Book>;
    filters: ShelfFilters;
    statuses: StatusOption[];
    languages: LanguageSummary[];
    languageOptions: LanguageOption[];
    sortOptions: SortOption[];
    statusCounts: Record<string, number>;
    stats: ShelfSummary;
    shelfUrl: string | null;
}>();

const hasBooks = computed(() => props.stats.total > 0);

const hasActiveFilters = computed(
    () =>
        Boolean(props.filters.search) ||
        Boolean(props.filters.status) ||
        Boolean(props.filters.language) ||
        props.filters.favorite,
);
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1.5">
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    Rak buku saya
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ formatNumber(props.stats.total) }} buku tersimpan ·
                    {{ formatNumber(props.stats.finished) }} sudah tamat
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button as-child>
                    <Link :href="create()">
                        <Plus class="size-4" />
                        Tambah buku
                    </Link>
                </Button>
                <ShareButton
                    v-if="props.shelfUrl"
                    :url="props.shelfUrl"
                    title="Rak buku saya"
                    label="Bagikan"
                />
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard
                label="Total buku"
                :value="formatNumber(props.stats.total)"
                :icon="Library"
            />
            <StatCard
                label="Sedang dibaca"
                :value="formatNumber(props.stats.reading)"
            />
            <StatCard
                label="Rata-rata rating"
                :value="props.stats.average_rating || '—'"
                :hint="`${formatNumber(props.stats.favorites)} favorit`"
            />
        </section>

        <ShelfFilterBar
            :filters="props.filters"
            :statuses="props.statuses"
            :languages="props.languages"
            :sort-options="props.sortOptions"
            :status-counts="props.statusCounts"
            :url="booksIndex.url()"
            :total="props.books.total"
        />

        <template v-if="props.books.data.length > 0">
            <div
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <BookCard
                    v-for="book in props.books.data"
                    :key="book.id"
                    :book="book"
                    :statuses="props.statuses"
                    owner
                />
            </div>

            <ShelfPagination :paginator="props.books" />
        </template>

        <EmptyState
            v-else-if="hasActiveFilters"
            title="Tidak ada buku yang cocok"
            description="Coba ubah kata kunci pencarian atau kosongkan filternya."
            :icon="SearchX"
        >
            <Button as-child variant="outline">
                <Link :href="booksIndex()">Bersihkan filter</Link>
            </Button>
        </EmptyState>

        <EmptyState
            v-else
            title="Rak kamu masih kosong"
            description="Tambahkan buku yang sudah, sedang, atau ingin kamu baca."
            :icon="Library"
        >
            <Button as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Tambah buku pertama
                </Link>
            </Button>
            <Button as-child variant="ghost">
                <Link :href="dashboard()">Kembali ke dashboard</Link>
            </Button>
        </EmptyState>
    </div>
</template>
