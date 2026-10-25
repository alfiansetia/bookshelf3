<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Database,
    EyeOff,
    Lock,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { contact, cookie } from '@/routes/legal';

const props = defineProps<{
    canonical: string;
    updatedAt: string;
}>();

const principles = [
    {
        icon: Database,
        title: 'Seperlunya saja',
        description:
            'Hanya menyimpan data akun Google dan isi rak yang kamu buat. Tidak ada penambangan data.',
    },
    {
        icon: EyeOff,
        title: 'Tanpa pelacak iklan',
        description:
            'Tidak ada cookie iklan atau pelacak pihak ketiga. Daftar cookie lengkap ada di halaman cookie.',
    },
    {
        icon: Lock,
        title: 'Kamu yang pegang kendali',
        description:
            'Rak bisa dibuat privat, profil bisa diubah, dan akun beserta datanya bisa dihapus.',
    },
];
</script>

<template>
    <SeoHead
        title="Kebijakan privasi"
        description="Kebijakan privasi Rak Buku: data akun Google dan isi rak yang dikumpulkan, cara dipakai, cara menghapusnya, dan cara menghubungi kami."
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
                <ShieldCheck class="size-3.5" />
                Kebijakan privasi
            </span>

            <h1
                class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
            >
                Datamu milikmu, kami hanya menjaganya
            </h1>

            <p class="text-base leading-relaxed text-muted-foreground">
                Rak Buku adalah katalog buku pribadi yang bisa dibagikan.
                Halaman ini menjelaskan data apa yang kami simpan, untuk apa,
                dan bagaimana kamu bisa melihat, mengubah, atau menghapusnya.
            </p>
        </header>

        <section class="mt-10 grid gap-4 sm:grid-cols-3">
            <article
                v-for="item in principles"
                :key="item.title"
                class="space-y-2 rounded-2xl border border-border/70 bg-card p-5"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-full bg-primary/10 text-primary"
                >
                    <component :is="item.icon" class="size-4" />
                </span>
                <h2 class="font-medium text-foreground">{{ item.title }}</h2>
                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ item.description }}
                </p>
            </article>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">
                Data yang kami kumpulkan
            </h2>
            <ul class="list-disc space-y-2 pl-5 text-sm leading-relaxed text-muted-foreground">
                <li>
                    <span class="font-medium text-foreground">Data akun Google:</span>
                    nama, alamat email, dan foto profil saat kamu masuk atau
                    mendaftar. Kami tidak menyimpan kata sandi apa pun karena
                    masuk hanya lewat Google.
                </li>
                <li>
                    <span class="font-medium text-foreground">Isi rakmu:</span>
                    username, tagline, tema rak, daftar buku, status baca,
                    rating, catatan, serta tautan sosial yang kamu pasang di
                    profil publik.
                </li>
                <li>
                    <span class="font-medium text-foreground">Gambar yang kamu unggah:</span>
                    sampul buku dan foto profil, disimpan sebagai WebP di
                    penyimpanan aplikasi.
                </li>
                <li>
                    <span class="font-medium text-foreground">Data teknis minimal:</span>
                    cookie sesi, cookie keamanan, dan pilihan persetujuan
                    cookie. Rinciannya ada di
                    <Link :href="cookie()" class="text-primary underline underline-offset-4 hover:no-underline">
                        kebijakan cookie</Link
                    >.
                </li>
            </ul>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">
                Cara data dipakai
            </h2>
            <ul class="list-disc space-y-2 pl-5 text-sm leading-relaxed text-muted-foreground">
                <li>Menjalankan layanan: sesi masuk, menyimpan rak, dan menampilkan profil publikmu.</li>
                <li>Menjaga keamanan: mencegah penyalahgunaan, spam, dan konten yang melanggar.</li>
                <li>Mengingat preferensimu: misalnya pilihan tema terang atau gelap.</li>
                <li>
                    Kami tidak menjual data, tidak menayangkan iklan, dan
                    tidak membagikan data ke broker data.
                </li>
            </ul>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">Hak dan kendalimu</h2>
            <div class="space-y-3 rounded-2xl border border-border/70 bg-card p-5 text-sm leading-relaxed text-muted-foreground">
                <p class="flex gap-2">
                    <EyeOff class="mt-0.5 size-4 shrink-0 text-primary" />
                    Jadikan rak tidak publik dari pengaturan bila kamu ingin catatanmu hanya terlihat sendiri.
                </p>
                <p class="flex gap-2">
                    <Database class="mt-0.5 size-4 shrink-0 text-primary" />
                    Ubah nama, tagline, tema, buku, dan tautan sosial kapan saja dari dashboard dan pengaturan.
                </p>
                <p class="flex gap-2">
                    <Trash2 class="mt-0.5 size-4 shrink-0 text-primary" />
                    Hapus akun beserta datanya dari pengaturan. Username dan identitas bekas akun ditahan selama masa
                    tunggu agar tidak disalahgunakan, lalu dibuka kembali.
                </p>
            </div>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">Penyimpanan dan keamanan</h2>
            <p class="text-sm leading-relaxed text-muted-foreground">
                Data disimpan di infrastruktur aplikasi dan cadangannya.
                Akses dibatasi untuk operasional layanan, misalnya menangani
                laporan penyalahgunaan atau gangguan teknis. Rak yang kamu
                tandai privat tidak dimasukkan ke mesin pencari dan tidak
                ditampilkan di halaman jelajah.
            </p>
        </section>

        <section
            class="mt-10 space-y-4 rounded-2xl border border-border/70 bg-muted/40 p-6"
        >
            <h2 class="font-display text-xl text-foreground">Ada pertanyaan soal data?</h2>
            <p class="text-sm leading-relaxed text-muted-foreground">
                Hubungi kami lewat halaman kontak. Sertakan alamat email akunmu
                supaya kami bisa menemukan dan membantumu lebih cepat.
            </p>
            <Button as-child>
                <Link :href="contact()">Hubungi kami</Link>
            </Button>
        </section>

        <footer
            class="mt-10 border-t border-border/70 pt-6 text-xs text-muted-foreground"
        >
            <p>Terakhir diperbarui {{ props.updatedAt }}.</p>
        </footer>
    </div>
</template>
