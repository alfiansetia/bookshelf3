<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Cookie, Settings2, ShieldCheck, SlidersHorizontal } from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { openCookieConsent } from '@/lib/cookieConsent';
import { home } from '@/routes';

const props = defineProps<{
    sessionCookie: string;
    canonical: string;
    updatedAt: string;
}>();

const { appearance } = useAppearance();

/**
 * Daftar cookie yang benar-benar dipakai aplikasi.
 */
const cookies = computed(() => [
    {
        name: props.sessionCookie,
        category: 'Esensial',
        purpose:
            'Menyimpan identitas sesi agar kamu tetap masuk saat berpindah halaman.',
        duration: 'Berakhir saat keluar atau setelah sesi kedaluwarsa.',
    },
    {
        name: 'XSRF-TOKEN',
        category: 'Esensial',
        purpose:
            'Melindungi formulir dan permintaan data dari pemalsuan permintaan antar situs.',
        duration: 'Berakhir saat sesi berakhir.',
    },
    {
        name: 'cookie_consent',
        category: 'Esensial',
        purpose:
            'Mengingat pilihan kategori cookie yang kamu tentukan di situs ini.',
        duration: '1 tahun.',
    },
    {
        name: 'appearance',
        category: 'Preferensi',
        purpose:
            'Menyimpan pilihan tema terang atau gelap supaya tidak perlu diatur ulang.',
        duration: '1 tahun.',
    },
]);

const categories = [
    {
        title: 'Cookie esensial',
        description:
            'Dibutuhkan agar situs bisa bekerja: menjaga sesi masuk, mengamankan formulir, dan menyimpan pilihan cookie. Kategori ini selalu aktif karena tanpa itu layanan tidak bisa dijalankan.',
        icon: ShieldCheck,
    },
    {
        title: 'Cookie preferensi',
        description:
            'Mengingat pengaturan tampilan pribadimu, seperti tema terang atau gelap. Kategori ini hanya aktif setelah kamu menyetujuinya, dan bisa dicabut kapan saja.',
        icon: SlidersHorizontal,
    },
];
</script>

<template>
    <SeoHead
        title="Kebijakan cookie"
        description="Penjelasan cookie yang dipakai Rak Buku: kategori esensial dan preferensi, tujuannya, serta cara mengubah pilihanmu."
        :canonical="props.canonical"
    />

    <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6 sm:py-14">
        <Button as-child variant="ghost" size="sm" class="-ml-3 mb-6">
            <Link :href="home()">
                <ArrowLeft class="size-4" />
                Kembali ke beranda
            </Link>
        </Button>

        <header class="space-y-4">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
            >
                <Cookie class="size-3.5" />
                Kebijakan cookie
            </span>

            <h1
                class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
            >
                Cookie yang kami pakai, dan kenapa
            </h1>

            <p class="text-base leading-relaxed text-muted-foreground">
                Kami memakai cookie sesedikit mungkin. Tidak ada cookie iklan
                atau pelacak pihak ketiga di situs ini. Kategori esensial
                dibutuhkan agar layanan berjalan, sedangkan kategori preferensi
                baru aktif setelah kamu menyetujuinya.
            </p>
        </header>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">
                Kategori cookie
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <article
                    v-for="category in categories"
                    :key="category.title"
                    class="space-y-2 rounded-2xl border border-border/70 bg-card p-5"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <component :is="category.icon" class="size-4" />
                    </span>
                    <h3 class="font-medium text-foreground">
                        {{ category.title }}
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ category.description }}
                    </p>
                </article>
            </div>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">
                Daftar cookie
            </h2>

            <div
                class="overflow-x-auto rounded-2xl border border-border/70 bg-card"
            >
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="bg-muted/50 text-xs text-muted-foreground uppercase">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Nama
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Kategori
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Tujuan
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Masa berlaku
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="item in cookies" :key="item.name">
                            <td
                                class="px-4 py-3 font-mono text-xs text-foreground"
                            >
                                {{ item.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ item.category }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ item.purpose }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ item.duration }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-muted-foreground">
                Cookie sesi disimpan di server dan hanya berlaku untuk
                permintaan dari peramban kamu. Cookie
                <span class="font-mono text-xs text-foreground">
                    cookie_consent </span
                > serta
                <span class="font-mono text-xs text-foreground">
                    appearance </span
                > dibuat oleh peramban dan bisa kamu hapus kapan saja lewat
                pengaturan peramban.
            </p>
        </section>

        <section
            class="mt-10 space-y-4 rounded-2xl border border-border/70 bg-muted/40 p-6"
        >
            <h2 class="font-display text-xl text-foreground">
                Mengubah pilihanmu
            </h2>
            <p class="text-sm leading-relaxed text-muted-foreground">
                Kamu bisa meninjau ulang pilihan cookie kapan saja. Menolak
                cookie preferensi akan menghapus tema tersimpan, sehingga
                tampilan kembali mengikuti pengaturan perangkatmu.
            </p>
            <Button type="button" @click="openCookieConsent">
                <Settings2 class="size-4" />
                Atur preferensi cookie
            </Button>
        </section>

        <footer
            class="mt-10 border-t border-border/70 pt-6 text-xs text-muted-foreground"
        >
            <p>
                Terakhir diperbarui {{ props.updatedAt }}. Tema yang sedang
                aktif: {{ appearance === 'system' ? 'mengikuti sistem' : appearance }}.
            </p>
        </footer>
    </div>
</template>
