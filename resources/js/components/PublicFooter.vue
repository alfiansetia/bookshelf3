<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Heart, Library } from '@lucide/vue';
import { explore, home, login, register } from '@/routes';
import { index as booksIndex } from '@/routes/books';

const page = usePage();
const year = new Date().getFullYear();

const columns = [
    {
        title: 'Jelajahi',
        links: [
            { label: 'Beranda', href: home() },
            { label: 'Rak publik', href: explore() },
        ],
    },
    {
        title: 'Akun',
        links: [
            { label: 'Masuk', href: login() },
            { label: 'Daftar gratis', href: register() },
            { label: 'Rak saya', href: booksIndex() },
        ],
    },
];
</script>

<template>
    <footer class="mt-16 border-t border-border/70 bg-muted/30">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-3 lg:col-span-2">
                    <Link :href="home()" class="flex items-center gap-2">
                        <span
                            class="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                        >
                            <Library class="size-5" />
                        </span>
                        <span class="font-display text-lg text-foreground">
                            {{ page.props.name }}
                        </span>
                    </Link>
                    <p
                        class="max-w-sm text-sm leading-relaxed text-muted-foreground"
                    >
                        Katalog buku pribadi yang bisa dibagikan. Simpan buku
                        yang sudah kamu baca, tulis catatanmu, lalu pamerkan
                        raknya lewat satu tautan.
                    </p>
                </div>

                <div
                    v-for="column in columns"
                    :key="column.title"
                    class="space-y-3"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.18em] text-foreground uppercase"
                    >
                        {{ column.title }}
                    </p>
                    <ul class="space-y-2">
                        <li v-for="link in column.links" :key="link.label">
                            <Link
                                :href="link.href"
                                class="text-sm text-muted-foreground transition-colors hover:text-primary"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-10 flex flex-col items-start justify-between gap-3 border-t border-border/70 pt-6 text-xs text-muted-foreground sm:flex-row sm:items-center"
            >
                <p>
                    &copy; {{ year }} {{ page.props.name }}. Dibuat untuk para
                    pembaca.
                </p>
                <p class="inline-flex items-center gap-1.5">
                    Dibuat dengan
                    <Heart class="size-3.5 fill-current text-rose-500" />
                    di Indonesia
                </p>
            </div>
        </div>
    </footer>
</template>
