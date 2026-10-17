<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, EyeOff, LayoutGrid, LogIn } from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { dashboard, explore, login } from '@/routes';

const props = defineProps<{
    username: string;
    context: 'profile' | 'book';
}>();

const page = usePage();
const viewer = computed(() => page.props.auth.user);

const title = 'Rak ini privat';

const description = computed(() =>
    props.context === 'book'
        ? `Buku ini ada di rak privat milik @${props.username}, jadi hanya pemiliknya yang bisa membukanya.`
        : `@${props.username} mengatur raknya sebagai privat, jadi koleksi bukunya hanya bisa dilihat pemiliknya.`,
);
</script>

<template>
    <SeoHead
        :title="`${title} — @${props.username}`"
        description="Rak ini diatur sebagai privat oleh pemiliknya."
        noindex
    />

    <div class="mx-auto w-full max-w-2xl px-4 py-16 sm:px-6 sm:py-24">
        <div
            class="flex flex-col items-center gap-4 rounded-3xl border border-border/70 bg-card px-6 py-12 text-center shadow-xs"
        >
            <span
                class="flex size-14 items-center justify-center rounded-full bg-muted text-muted-foreground"
            >
                <EyeOff class="size-7" />
            </span>

            <div class="space-y-2">
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    {{ title }}
                </h1>
                <p class="mx-auto max-w-md text-sm leading-relaxed text-balance text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                <Button as-child>
                    <Link :href="explore()">
                        <Compass class="size-4" />
                        Jelajahi rak publik
                    </Link>
                </Button>

                <Button v-if="viewer" as-child variant="outline">
                    <Link :href="dashboard()">
                        <LayoutGrid class="size-4" />
                        Kembali ke dashboard
                    </Link>
                </Button>

                <Button v-else as-child variant="outline">
                    <Link :href="login()">
                        <LogIn class="size-4" />
                        Masuk, kalau ini rakmu
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>
