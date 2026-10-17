<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Sparkles } from '@lucide/vue';
import BookForm from '@/components/bookshelf/BookForm.vue';
import { Button } from '@/components/ui/button';
import { index as booksIndex } from '@/routes/books';
import type { LanguageOption, StatusOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Rak saya', href: booksIndex() },
            { title: 'Tambah buku', href: '' },
        ],
    },
});

defineProps<{
    statuses: StatusOption[];
    languages: LanguageOption[];
    coverMaxKilobytes: number;
}>();
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

            <div class="space-y-1.5">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                >
                    <Sparkles class="size-3.5" />
                    Buku baru
                </span>
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    Tambah buku ke rak
                </h1>
                <p class="max-w-xl text-sm text-muted-foreground">
                    Hanya judul dan penulis yang wajib diisi. Kolom lain bisa
                    kamu lengkapi kapan saja saat ingatannya muncul.
                </p>
            </div>
        </div>

        <BookForm
            :statuses="statuses"
            :languages="languages"
            :cover-max-kilobytes="coverMaxKilobytes"
        />
    </div>
</template>
