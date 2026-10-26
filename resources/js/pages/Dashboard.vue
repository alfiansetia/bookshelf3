<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Heart,
    Library,
    Plus,
    Sparkles,
    Star,
    TrendingUp,
} from '@lucide/vue';
import BookCard from '@/components/bookshelf/BookCard.vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import SectionHeading from '@/components/bookshelf/SectionHeading.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import StatCard from '@/components/bookshelf/StatCard.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import StatusBadge from '@/components/bookshelf/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { iconByName } from '@/lib/icons';
import { formatNumber } from '@/lib/format';
import { statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import { dashboard, explore } from '@/routes';
import { create, index as booksIndex } from '@/routes/books';
import { edit as editShelf } from '@/routes/shelf';
import type { Book, ShelfSummary, StatusOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const props = defineProps<{
    stats: ShelfSummary & { languages: number };
    statuses: StatusOption[];
    statusCounts: Record<string, number>;
    currentlyReading: Book[];
    recentlyAdded: Book[];
    favorites: Book[];
    topRated: Book[];
    languages: { value: string; label: string; total: number }[];
    shelfUrl: string | null;
    share: { url: string | null; public: boolean };
}>();

const page = usePage();
const userName = computed(() => page.props.auth.user?.name ?? '');

const hasBooks = computed(() => props.stats.total > 0);

const totalForBar = computed(() =>
    Object.values(props.statusCounts).reduce(
        (total, count) => total + count,
        0,
    ),
);
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between sm:p-6"
        >
            <div class="space-y-1.5">
                <p
                    class="text-xs font-semibold tracking-[0.18em] text-primary uppercase"
                >
                    Rak kamu
                </p>
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    Halo, {{ userName }} 👋
                </h1>
                <p class="text-sm text-muted-foreground">
                    <template v-if="props.share.public && props.share.url">
                        Rakmu sudah aktif dan bisa dibuka lewat
                        <span class="font-medium text-primary">{{
                            props.share.url
                        }}</span>
                    </template>
                    <template v-else-if="hasBooks">
                        Rakmu masih disembunyikan dari publik. Aktifkan
                        visibilitasnya di pengaturan gaya rak.
                    </template>
                    <template v-else>
                        Mulai dengan menambahkan buku pertama ke rakmu.
                    </template>
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
                    v-if="props.share.url"
                    :url="props.share.url"
                    title="Rak buku saya"
                    label="Bagikan rak"
                />

                <Button as-child variant="outline">
                    <Link :href="editShelf()">
                        <Sparkles class="size-4" />
                        Gaya rak
                    </Link>
                </Button>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                label="Total buku"
                :value="formatNumber(props.stats.total)"
                :icon="Library"
                tone="primary"
            />
            <StatCard
                label="Sudah tamat"
                :value="formatNumber(props.stats.finished)"
                :icon="BookOpen"
                :hint="`${props.stats.languages} bahasa`"
            />
            <StatCard
                label="Sedang dibaca"
                :value="formatNumber(props.stats.reading)"
                :icon="TrendingUp"
            />
            <StatCard
                label="Favorit"
                :value="formatNumber(props.stats.favorites)"
                :icon="Heart"
                tone="accent"
            />
        </section>

        <section
            v-if="hasBooks"
            class="space-y-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
        >
            <SectionHeading
                eyebrow="Sebaran status"
                title="Komposisi rak kamu"
            />

            <div class="flex h-3 w-full overflow-hidden rounded-full bg-muted">
                <span
                    v-for="status in props.statuses"
                    :key="status.value"
                    v-show="(props.statusCounts[status.value] ?? 0) > 0"
                    :class="cn('h-full', statusStyle(status.color).dot)"
                    :style="{
                        width: `${((props.statusCounts[status.value] ?? 0) / Math.max(totalForBar, 1)) * 100}%`,
                    }"
                    :title="`${status.label}: ${props.statusCounts[status.value] ?? 0}`"
                />
            </div>

            <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <li
                    v-for="status in props.statuses"
                    :key="status.value"
                    class="flex items-center justify-between gap-2 rounded-xl border border-border/70 px-3 py-2"
                >
                    <span
                        class="inline-flex items-center gap-2 text-sm text-foreground"
                    >
                        <span
                            :class="
                                cn(
                                    'size-2.5 rounded-full',
                                    statusStyle(status.color).dot,
                                )
                            "
                        />
                        {{ status.label }}
                    </span>
                    <span class="text-sm font-medium text-muted-foreground">
                        {{
                            formatNumber(props.statusCounts[status.value] ?? 0)
                        }}
                    </span>
                </li>
            </ul>
        </section>

        <EmptyState
            v-if="!hasBooks"
            title="Rak kamu masih kosong"
            description="Tambahkan buku pertama, lalu susun raknya sesuai gaya favoritmu. Butuh inspirasi? Lihat rak pembaca lain dulu."
            :icon="Library"
        >
            <Button as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Tambah buku pertama
                </Link>
            </Button>
            <Button as-child variant="outline">
                <Link :href="explore()">Jelajahi rak lain</Link>
            </Button>
        </EmptyState>

        <template v-else>
            <section v-if="props.currentlyReading.length > 0" class="space-y-4">
                <div class="flex items-end justify-between gap-4">
                    <SectionHeading
                        eyebrow="Sedang dibaca"
                        title="Lanjutkan bacaanmu"
                    />
                    <Button as-child variant="ghost" size="sm">
                        <Link
                            :href="booksIndex({ query: { status: 'reading' } })"
                        >
                            Lihat semua
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="book in props.currentlyReading"
                        :key="book.id"
                        :href="book.public_url ?? booksIndex()"
                        class="flex items-center gap-3 rounded-2xl border border-border/70 bg-card p-3 shadow-xs transition-shadow hover:shadow-md"
                    >
                        <BookCover :book="book" size="sm" />
                        <div class="min-w-0 space-y-1">
                            <p
                                class="line-clamp-2 text-sm font-medium text-foreground"
                            >
                                {{ book.title }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ book.author }}
                            </p>
                            <StatusBadge
                                :status="book.status"
                                :label="book.status_label"
                                :statuses="props.statuses"
                            />
                        </div>
                    </Link>
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-end justify-between gap-4">
                    <SectionHeading
                        eyebrow="Baru masuk"
                        title="Terakhir ditambahkan"
                    />
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="booksIndex()">
                            Kelola rak
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>

                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <BookCard
                        v-for="book in props.recentlyAdded"
                        :key="book.id"
                        :book="book"
                        :statuses="props.statuses"
                        owner
                    />
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="space-y-4">
                    <SectionHeading eyebrow="Favorit" title="Buku kesayangan" />

                    <ul
                        v-if="props.favorites.length > 0"
                        class="space-y-2 rounded-2xl border border-border/70 bg-card p-3 shadow-xs"
                    >
                        <li
                            v-for="book in props.favorites"
                            :key="book.id"
                            class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-muted/60"
                        >
                            <BookCover :book="book" size="xs" />
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-medium text-foreground"
                                >
                                    {{ book.title }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ book.author }}
                                </p>
                            </div>
                            <StarRating :value="book.rating" size="sm" />
                        </li>
                    </ul>

                    <p v-else class="text-sm text-muted-foreground">
                        Belum ada buku yang ditandai favorit.
                    </p>
                </section>

                <section class="space-y-4">
                    <SectionHeading
                        eyebrow="Rating tertinggi"
                        title="Paling berkesan"
                    />

                    <ul
                        v-if="props.topRated.length > 0"
                        class="space-y-2 rounded-2xl border border-border/70 bg-card p-3 shadow-xs"
                    >
                        <li
                            v-for="book in props.topRated"
                            :key="book.id"
                            class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-muted/60"
                        >
                            <BookCover :book="book" size="xs" />
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-medium text-foreground"
                                >
                                    {{ book.title }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ book.author }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 text-sm font-medium text-amber-600 dark:text-amber-400"
                            >
                                <Star class="size-3.5 fill-current" />
                                {{ formatNumber(book.rating) }}
                            </span>
                        </li>
                    </ul>

                    <p v-else class="text-sm text-muted-foreground">
                        Belum ada buku yang diberi rating.
                    </p>
                </section>
            </div>

            <section
                v-if="props.languages.length > 0"
                class="flex flex-wrap items-center gap-2 rounded-2xl border border-border/70 bg-card p-4 shadow-xs"
            >
                <span class="text-sm text-muted-foreground"
                    >Bahasa di rakmu:</span
                >
                <span
                    v-for="language in props.languages"
                    :key="language.value"
                    class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs text-foreground"
                >
                    {{ language.label }}
                    <span class="text-muted-foreground">
                        {{ formatNumber(language.total) }}
                    </span>
                </span>
            </section>
        </template>
    </div>
</template>
