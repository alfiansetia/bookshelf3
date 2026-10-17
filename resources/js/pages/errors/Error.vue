<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    Compass,
    Home as HomeIcon,
    LifeBuoy,
    RefreshCw,
    SearchX,
    ServerCrash,
    ShieldAlert,
    TimerOff,
} from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { explore, home } from '@/routes';

const props = defineProps<{
    status: number;
}>();

type ErrorContent = {
    title: string;
    description: string;
    icon: LucideIcon;
    canRetry: boolean;
};

/**
 * Judul, penjelasan, dan ikon untuk tiap kode status yang ditangani.
 */
const contents: Record<number, ErrorContent> = {
    403: {
        title: 'Akses ditolak',
        description:
            'Kamu tidak punya izin untuk membuka halaman ini. Kalau ini terasa salah, coba masuk dengan akun yang tepat.',
        icon: ShieldAlert,
        canRetry: false,
    },
    404: {
        title: 'Halaman tidak ditemukan',
        description:
            'Halaman yang kamu cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah ketik.',
        icon: SearchX,
        canRetry: false,
    },
    419: {
        title: 'Sesi kamu sudah berakhir',
        description:
            'Halaman ini kedaluwarsa karena terlalu lama tidak ada aktivitas. Muat ulang halaman lalu coba lagi.',
        icon: TimerOff,
        canRetry: true,
    },
    429: {
        title: 'Terlalu banyak permintaan',
        description:
            'Kamu mengirim permintaan terlalu sering dalam waktu singkat. Tunggu sebentar, lalu coba lagi.',
        icon: LifeBuoy,
        canRetry: true,
    },
    500: {
        title: 'Terjadi kesalahan di server',
        description:
            'Ada yang tidak beres di sisi kami. Tim sudah menerima laporannya, silakan coba beberapa saat lagi.',
        icon: ServerCrash,
        canRetry: true,
    },
    503: {
        title: 'Layanan sedang sibuk',
        description:
            'Kami sedang melakukan pemeliharaan singkat. Terima kasih sudah menunggu, silakan kembali sebentar lagi.',
        icon: ServerCrash,
        canRetry: true,
    },
};

const fallback: ErrorContent = {
    title: 'Terjadi kesalahan',
    description:
        'Permintaanmu tidak bisa diproses saat ini. Coba lagi sebentar lagi.',
    icon: ServerCrash,
    canRetry: true,
};

const content = computed<ErrorContent>(() => contents[props.status] ?? fallback);

function retry(): void {
    router.reload();
}
</script>

<template>
    <SeoHead
        :title="`${props.status} — ${content.title}`"
        :description="content.description"
        noindex
    />

    <div class="mx-auto w-full max-w-2xl px-4 py-16 sm:px-6 sm:py-24">
        <div
            class="flex flex-col items-center gap-5 rounded-3xl border border-border/70 bg-card px-6 py-12 text-center shadow-xs"
        >
            <span
                class="flex size-14 items-center justify-center rounded-full bg-muted text-muted-foreground"
            >
                <component :is="content.icon" class="size-7" />
            </span>

            <p
                class="font-display text-5xl leading-none text-primary tabular-nums"
            >
                {{ props.status }}
            </p>

            <div class="space-y-2">
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    {{ content.title }}
                </h1>
                <p
                    class="mx-auto max-w-md text-sm leading-relaxed text-balance text-muted-foreground"
                >
                    {{ content.description }}
                </p>
            </div>

            <div class="mt-1 flex flex-wrap items-center justify-center gap-2">
                <Button v-if="content.canRetry" type="button" @click="retry">
                    <RefreshCw class="size-4" />
                    Coba lagi
                </Button>
                <Button v-else as-child>
                    <Link :href="home()">
                        <HomeIcon class="size-4" />
                        Kembali ke beranda
                    </Link>
                </Button>

                <Button as-child variant="outline">
                    <Link :href="explore()">
                        <Compass class="size-4" />
                        Jelajahi rak
                    </Link>
                </Button>

                <Button v-if="content.canRetry" as-child variant="ghost">
                    <Link :href="home()">
                        <HomeIcon class="size-4" />
                        Kembali ke beranda
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>
