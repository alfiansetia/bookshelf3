<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ChevronDown, CircleHelp, MessageCircle } from '@lucide/vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { contact, privacy } from '@/routes/legal';

const props = defineProps<{
    canonical: string;
    updatedAt: string;
}>();

const faqs = [
    {
        question: 'Apa itu Rak Buku?',
        answer: 'Rak Buku adalah katalog buku pribadi yang bisa dibagikan. Kamu mencatat buku yang sedang dan sudah dibaca, memberi rating dan catatan, lalu membagikan semuanya lewat satu tautan seperti namamu.',
    },
    {
        question: 'Apakah gratis?',
        answer: 'Ya, gratis untuk membuat akun, menyusun rak, dan membagikannya. Cukup masuk dengan akun Google, pilih username rak, lalu tambah buku pertamamu.',
    },
    {
        question: 'Bagaimana cara masuk dan daftar?',
        answer: 'Aplikasi ini hanya memakai tombol Google — tidak ada kata sandi yang perlu diingat. Pilih Masuk atau Daftar gratis, lanjutkan dengan Google, lalu selesaikan pemilihan username di halaman onboarding.',
    },
    {
        question: 'Bagaimana cara membagikan rak saya?',
        answer: 'Rak publikmu hidup di alamat username kamu, misalnya /usernamemu. Buka profil publikmu lalu salin tautan dari bilah alamat dan bagikan ke teman atau media sosial.',
    },
    {
        question: 'Bisakah rak saya dibuat privat?',
        answer: 'Bisa. Atur visibilitas rak dari pengaturan supaya tidak tampil di halaman jelajah dan tidak terindeks mesin pencari. Kamu tetap bisa melihat dan mengelola bukumu setelah masuk.',
    },
    {
        question: 'Data apa saja yang disimpan tentang saya?',
        answer: 'Data akun Google (nama, email, foto), isi rak (buku, status, rating, catatan), gambar yang kamu unggah, dan cookie teknis minimal. Rincian lengkapnya ada di halaman kebijakan privasi.',
    },
    {
        question: 'Bagaimana cara menghapus akun dan data saya?',
        answer: 'Hapus akun dari halaman pengaturan keamanan. Data rak ikut terhapus, sedangkan identitas bekas akun ditahan selama masa tunggu agar username tidak langsung dicatut orang lain.',
    },
    {
        question: 'Apakah ada aplikasi seluler?',
        answer: 'Belum ada aplikasi khusus, tapi situs ini bisa dipasang sebagai aplikasi web (PWA) dari peramban di ponsel maupun laptop sehingga bisa dibuka seperti aplikasi biasa.',
    },
    {
        question: 'Saya menemukan bug atau punya saran fitur. Ke mana melapor?',
        answer: 'Kirim email beserta tangkapan layar dan langkah mereproduksinya lewat halaman kontak. Sertakan alamat email akunmu supaya mudah ditelusuri.',
    },
];

const openIndex = ref<number | null>(0);

const structuredData = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((item) => ({
        '@type': 'Question',
        name: item.question,
        acceptedAnswer: {
            '@type': 'Answer',
            text: item.answer,
        },
    })),
}));

function toggle(index: number): void {
    openIndex.value = openIndex.value === index ? null : index;
}
</script>

<template>
    <SeoHead
        title="FAQ"
        description="Jawaban atas pertanyaan umum soal Rak Buku: akun Google, berbagi rak, privasi, hapus akun, dan cara menghubungi kami."
        :canonical="props.canonical"
    />
    <JsonLd :data="structuredData" />

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
                <CircleHelp class="size-3.5" />
                FAQ
            </span>

            <h1
                class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
            >
                Pertanyaan yang sering ditanyakan
            </h1>

            <p class="text-base leading-relaxed text-muted-foreground">
                Semua hal dasar soal akun, rak publik, privasi, dan bantuan
                dalam satu tempat.
            </p>
        </header>

        <section class="mt-10 space-y-3">
            <div
                v-for="(item, index) in faqs"
                :key="item.question"
                class="overflow-hidden rounded-2xl border border-border/70 bg-card"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                    :aria-expanded="openIndex === index"
                    @click="toggle(index)"
                >
                    <span class="font-medium text-foreground">{{ item.question }}</span>
                    <ChevronDown
                        :class="
                            cn(
                                'size-4 shrink-0 text-muted-foreground transition-transform',
                                openIndex === index && 'rotate-180',
                            )
                        "
                    />
                </button>
                <div v-show="openIndex === index" class="border-t border-border/70 px-5 py-4">
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ item.answer }}
                    </p>
                </div>
            </div>
        </section>

        <section
            class="mt-10 space-y-4 rounded-2xl border border-border/70 bg-muted/40 p-6"
        >
            <h2 class="font-display text-xl text-foreground">Masih belum terjawab?</h2>
            <p class="text-sm leading-relaxed text-muted-foreground">
                Baca kebijakan privasi untuk urusan data, atau hubungi kami
                langsung lewat halaman kontak.
            </p>
            <div class="flex flex-wrap gap-3">
                <Button as-child>
                    <Link :href="contact()">
                        <MessageCircle class="size-4" />
                        Hubungi kami
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="privacy()">Kebijakan privasi</Link>
                </Button>
            </div>
        </section>

        <footer
            class="mt-10 border-t border-border/70 pt-6 text-xs text-muted-foreground"
        >
            <p>Terakhir diperbarui {{ props.updatedAt }}.</p>
        </footer>
    </div>
</template>
