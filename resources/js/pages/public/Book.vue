<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    CalendarDays,
    ExternalLink,
    FileText,
    Languages,
    Pencil,
    Quote,
} from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import SectionHeading from '@/components/bookshelf/SectionHeading.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import StatusBadge from '@/components/bookshelf/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDateRange, formatNumber, readingTime } from '@/lib/format';
import { edit } from '@/routes/books';
import { show as shelfShow } from '@/routes/shelf';
import { show as shelfBookShow } from '@/routes/shelf/book';
import type { Book, ShelfProfile } from '@/types';

const props = defineProps<{
    profile: ShelfProfile;
    book: Book;
    related: Book[];
    isOwner: boolean;
    canonical: string;
}>();

const shelfUrl = computed(() => shelfShow.url(props.profile.username));

const shareUrl = computed(() =>
    shelfBookShow.url({
        username: props.profile.username,
        book: props.book.slug,
    }),
);

const readRange = computed(() =>
    formatDateRange(props.book.started_at, props.book.finished_at),
);

const pagesLabel = computed(() => {
    if (!props.book.total_pages) {
        return null;
    }

    const time = readingTime(props.book.total_pages);

    return time
        ? `${formatNumber(props.book.total_pages)} halaman · ${time}`
        : `${formatNumber(props.book.total_pages)} halaman`;
});

const metas = computed(() =>
    [
        props.book.publisher
            ? { icon: BookOpen, label: props.book.publisher }
            : null,
        props.book.published_year
            ? { icon: CalendarDays, label: String(props.book.published_year) }
            : null,
        { icon: Languages, label: props.book.language_label },
        pagesLabel.value ? { icon: FileText, label: pagesLabel.value } : null,
    ].filter(
        (item): item is { icon: typeof BookOpen; label: string } =>
            item !== null,
    ),
);
</script>

<template>
    <Head :title="`${props.book.title} — Rak ${props.profile.name}`">
        <link rel="canonical" :href="props.canonical" />
        <meta
            name="description"
            :content="
                props.book.synopsis ??
                `${props.book.title} oleh ${props.book.author}, ada di rak ${props.profile.name}.`
            "
        />
    </Head>

    <div class="mx-auto w-full max-w-6xl space-y-8 px-4 py-8 sm:px-6 sm:py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <Button as-child variant="ghost" size="sm" class="-ml-3">
                <Link :href="shelfUrl">
                    <ArrowLeft class="size-4" />
                    Kembali ke rak {{ props.profile.name }}
                </Link>
            </Button>

            <div class="flex flex-wrap items-center gap-2">
                <Button
                    v-if="props.isOwner && props.book.can.update"
                    as-child
                    size="sm"
                >
                    <Link :href="edit(props.book.id)">
                        <Pencil class="size-4" />
                        Ubah buku
                    </Link>
                </Button>
                <ShareButton
                    :url="shareUrl"
                    :title="props.book.title"
                    :text="`Lihat buku ${props.book.title} di rak ${props.profile.name}.`"
                    label="Bagikan buku"
                    size="sm"
                />
            </div>
        </div>

        <article class="grid gap-8 lg:grid-cols-[18rem_1fr] lg:items-start">
            <div class="mx-auto w-48 lg:mx-0 lg:w-full">
                <BookCover
                    :book="props.book"
                    size="xl"
                    class="w-full rounded-xl shadow-lg"
                />
            </div>

            <div class="space-y-5">
                <div class="space-y-3">
                    <StatusBadge
                        :status="props.book.status"
                        :label="props.book.status_label"
                        size="md"
                    />

                    <h1
                        class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
                    >
                        {{ props.book.title }}
                    </h1>

                    <p class="text-base text-muted-foreground">
                        oleh
                        <span class="font-medium text-foreground">
                            {{ props.book.author }}
                        </span>
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <StarRating :value="props.book.rating" show-value />
                        <span
                            v-if="readRange"
                            class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                        >
                            <CalendarDays class="size-3.5" />
                            {{ readRange }}
                        </span>
                    </div>
                </div>

                <ul class="flex flex-wrap gap-2">
                    <li
                        v-for="meta in metas"
                        :key="meta.label"
                        class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground"
                    >
                        <component :is="meta.icon" class="size-3.5" />
                        {{ meta.label }}
                    </li>
                </ul>

                <section v-if="props.book.synopsis" class="space-y-2">
                    <h2
                        class="text-xs font-semibold tracking-[0.18em] text-primary uppercase"
                    >
                        Sinopsis versi {{ props.profile.name }}
                    </h2>
                    <p
                        class="leading-relaxed text-pretty whitespace-pre-line text-foreground/90"
                    >
                        {{ props.book.synopsis }}
                    </p>
                </section>

                <section
                    v-if="props.book.review"
                    class="space-y-3 rounded-2xl border border-border/70 bg-card p-5"
                >
                    <h2
                        class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.18em] text-primary uppercase"
                    >
                        <Quote class="size-3.5" />
                        Review & kesan
                    </h2>
                    <p
                        class="leading-relaxed text-pretty whitespace-pre-line text-foreground/90"
                    >
                        {{ props.book.review }}
                    </p>
                </section>

                <section
                    v-if="props.profile.socials.length > 0"
                    class="flex flex-wrap items-center gap-2 border-t border-border/70 pt-5 text-sm text-muted-foreground"
                >
                    Ikuti {{ props.profile.name }}:
                    <a
                        v-for="social in props.profile.socials"
                        :key="social.platform"
                        :href="social.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-primary hover:underline"
                    >
                        {{ social.label }}
                        <ExternalLink class="size-3" />
                    </a>
                </section>
            </div>
        </article>

        <section v-if="props.related.length > 0" class="space-y-5">
            <SectionHeading
                eyebrow="Dari rak yang sama"
                title="Buku lain yang mungkin kamu suka"
            />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="item in props.related"
                    :key="item.id"
                    :href="item.public_url ?? shelfUrl"
                    class="group flex gap-3 rounded-2xl border border-border/70 bg-card p-3 shadow-xs transition-shadow hover:shadow-md"
                >
                    <BookCover :book="item" size="sm" />
                    <div class="min-w-0 space-y-1">
                        <p
                            class="line-clamp-2 text-sm font-medium text-foreground group-hover:text-primary"
                        >
                            {{ item.title }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ item.author }}
                        </p>
                        <StarRating :value="item.rating" size="sm" />
                    </div>
                </Link>
            </div>
        </section>

        <EmptyState
            v-else
            title="Belum ada buku lain di rak ini"
            :description="`Rak ${props.profile.name} baru berisi buku ini.`"
            :icon="BookOpen"
        />
    </div>
</template>
