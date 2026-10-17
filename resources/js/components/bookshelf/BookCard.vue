<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { BookCheck, Heart, Pencil, Trash2 } from '@lucide/vue';
import BookCover from '@/components/bookshelf/BookCover.vue';
import StarRating from '@/components/bookshelf/StarRating.vue';
import StatusBadge from '@/components/bookshelf/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate, formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';
import { destroy, edit } from '@/routes/books';
import type { Book, StatusOption } from '@/types';

const props = withDefaults(
    defineProps<{
        book: Book;
        statuses?: StatusOption[];
        owner?: boolean;
        class?: string;
    }>(),
    {
        statuses: () => [],
        owner: false,
    },
);

const detailHref = computed(() => props.book.public_url);

const finishedLabel = computed(() => {
    if (props.book.status !== 'finished' || !props.book.finished_at) {
        return null;
    }

    return formatDate(props.book.finished_at);
});

function remove(): void {
    if (!window.confirm(`Hapus "${props.book.title}" dari rak kamu?`)) {
        return;
    }

    router.delete(destroy.url(props.book.id), { preserveScroll: true });
}
</script>

<template>
    <article
        :class="
            cn(
                'group flex flex-col overflow-hidden rounded-2xl border border-border/70 bg-card shadow-xs transition-shadow hover:shadow-md',
                props.class,
            )
        "
    >
        <component
            :is="detailHref ? Link : 'div'"
            :href="detailHref ?? undefined"
            class="relative block bg-muted/40 p-4"
        >
            <BookCover
                :book="props.book"
                size="lg"
                class="mx-auto w-[58%] transition-transform duration-300 group-hover:-translate-y-1"
            />

            <span
                v-if="props.book.is_favorite"
                class="absolute top-3 right-3 flex size-8 items-center justify-center rounded-full bg-rose-500 text-white shadow-md"
                aria-label="Buku favorit"
            >
                <Heart class="size-4 fill-current" />
            </span>
        </component>

        <div class="flex flex-1 flex-col gap-3 p-4">
            <div class="space-y-1.5">
                <StatusBadge
                    :status="props.book.status"
                    :label="props.book.status_label"
                    :statuses="props.statuses"
                />
                <h3
                    class="line-clamp-2 leading-snug font-semibold text-foreground"
                >
                    {{ props.book.title }}
                </h3>
                <p class="truncate text-sm text-muted-foreground">
                    {{ props.book.author }}
                </p>
            </div>

            <div class="flex items-center justify-between gap-2">
                <StarRating :value="props.book.rating" size="sm" show-value />
                <span
                    v-if="props.book.total_pages"
                    class="text-xs text-muted-foreground"
                >
                    {{ formatNumber(props.book.total_pages) }} hlm
                </span>
            </div>

            <p
                v-if="finishedLabel"
                class="flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <BookCheck class="size-3.5 text-emerald-500" />
                Tamat {{ finishedLabel }}
            </p>

            <p
                class="line-clamp-3 text-xs leading-relaxed text-muted-foreground"
            >
                {{
                    props.book.synopsis ??
                    props.book.review ??
                    'Belum ada catatan.'
                }}
            </p>

            <div
                v-if="props.owner"
                class="mt-auto flex items-center gap-2 border-t border-border/70 pt-3"
            >
                <Button as-child variant="outline" size="sm" class="flex-1">
                    <Link :href="edit(props.book.id)">
                        <Pencil class="size-3.5" />
                        Ubah
                    </Link>
                </Button>

                <Button
                    v-if="props.book.can.delete"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="text-muted-foreground hover:text-destructive"
                    @click="remove"
                >
                    <Trash2 class="size-3.5" />
                    <span class="sr-only">Hapus {{ props.book.title }}</span>
                </Button>
            </div>

            <div
                v-else-if="props.book.public_url"
                class="mt-auto border-t border-border/70 pt-3"
            >
                <Button as-child variant="outline" size="sm" class="w-full">
                    <Link :href="props.book.public_url">Lihat detail</Link>
                </Button>
            </div>
        </div>
    </article>
</template>
