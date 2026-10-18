<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, LayoutGrid, LogIn, ShieldAlert } from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { dashboard, explore, login } from '@/routes';

const props = defineProps<{
    profile: {
        username: string | null;
        name: string;
        avatar_url: string | null;
    };
    context: 'profile' | 'book';
    reason: string;
    blockedAt: string | null;
    canonical: string;
}>();

const page = usePage();
const viewer = computed(() => page.props.auth.user);

const title = 'Akun ini diblokir';

const description = computed(() =>
    props.context === 'book'
        ? `Buku ini ada di rak @${props.profile.username} yang sedang diblokir, jadi isinya belum bisa dibuka.`
        : `Rak @${props.profile.username} sedang diblokir, jadi koleksi bukunya belum bisa dilihat.`,
);

const blockedSince = computed(() => formatDate(props.blockedAt));
</script>

<template>
    <SeoHead
        :title="`${title} — @${props.profile.username}`"
        :description="description"
        :canonical="props.canonical"
        noindex
    />

    <div class="mx-auto w-full max-w-2xl px-4 py-16 sm:px-6 sm:py-24">
        <div
            class="flex flex-col items-center gap-4 rounded-3xl border border-border/70 bg-card px-6 py-12 text-center shadow-xs"
        >
            <span
                class="flex size-14 items-center justify-center rounded-full bg-destructive/10 text-destructive"
            >
                <ShieldAlert class="size-7" />
            </span>

            <div class="space-y-2">
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    {{ title }}
                </h1>
                <p
                    class="mx-auto max-w-md text-sm leading-relaxed text-balance text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>

            <Alert variant="destructive" class="text-left">
                <ShieldAlert />
                <AlertTitle>Alasan dari admin</AlertTitle>
                <AlertDescription>
                    <p>{{ props.reason }}</p>
                    <p v-if="blockedSince" class="mt-1 text-xs opacity-80">
                        Diblokir sejak {{ blockedSince }}.
                    </p>
                </AlertDescription>
            </Alert>

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
                        Masuk
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>
