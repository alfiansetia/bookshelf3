<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink } from '@lucide/vue';
import BookForm from '@/components/bookshelf/BookForm.vue';
import { Button } from '@/components/ui/button';
import { index as booksIndex } from '@/routes/books';
import type { Book, LanguageOption, StatusOption } from '@/types';

const props = defineProps<{
    book: Book;
    statuses: StatusOption[];
    languages: LanguageOption[];
    coverMaxKilobytes: number;
    shelfUrl: string | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Rak saya', href: booksIndex() },
            { title: 'Ubah buku', href: booksIndex() },
        ],
    },
});
</script>

<template>
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div class="space-y-3">
            <Button as-child variant="ghost" size="sm" class="-ml-3">
                <Link :href="booksIndex()">
                    <ArrowLeft class="size-4" />
                    Kembali ke rak
                </Link>
            </Button>

            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="space-y-1.5">
                    <h1
                        class="font-display text-2xl text-foreground sm:text-3xl"
                    >
                        Ubah data buku
                    </h1>
                    <p class="max-w-xl text-sm text-muted-foreground">
                        Perbarui catatanmu tentang
                        <span class="font-medium text-foreground">
                            {{ props.book.title }}
                        </span>
                        supaya rak tetap akurat.
                    </p>
                </div>

                <Button
                    v-if="props.book.public_url"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <a
                        :href="props.book.public_url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="size-4" />
                        Lihat di rak publik
                    </a>
                </Button>
            </div>
        </div>

        <BookForm
            :book="props.book"
            :statuses="props.statuses"
            :languages="props.languages"
            :cover-max-kilobytes="props.coverMaxKilobytes"
        />
    </div>
</template>
