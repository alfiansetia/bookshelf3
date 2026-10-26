<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Compass,
    Heart,
    Languages,
    Palette,
    Quote,
    Share2,
    Sparkles,
    Star,
    Users,
} from '@lucide/vue';
import SectionHeading from '@/components/bookshelf/SectionHeading.vue';
import ShelfThemePreview from '@/components/bookshelf/ShelfThemePreview.vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import { Button } from '@/components/ui/button';
import { iconByName } from '@/lib/icons';
import { formatNumber } from '@/lib/format';
import { statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import { explore, login, register } from '@/routes';
import { show as shelfShow } from '@/routes/shelf';
import type { FeaturedShelf, ShelfThemeOption, StatusOption } from '@/types';

const props = defineProps<{
    themes: ShelfThemeOption[];
    statuses: StatusOption[];
    featuredShelves: FeaturedShelf[];
    canonical: string;
    numbers: {
        readers: number;
        books: number;
        finished: number;
    };
}>();

const description =
    'Rak buku pribadi yang bisa dibagikan. Catat buku yang sedang dan sudah kamu baca, beri rating dan catatan, lalu bagikan raknya lewat satu tautan.';

const structuredData = computed(() => [
    {
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        name: 'Rak Buku',
        url: props.canonical,
        description,
        inLanguage: 'id-ID',
        potentialAction: {
            '@type': 'SearchAction',
            target: {
                '@type': 'EntryPoint',
                urlTemplate: `${explore.url()}?search={search_term_string}`,
            },
            'query-input': 'required name=search_term_string',
        },
    },
]);

const features = [
    {
        icon: Palette,
        title: 'Lima gaya rak',
        description:
            'Pilih tampilan rak kayu klasik, minimalis, neon, kafe hangat, atau reel cerita. Ganti kapan saja tanpa kehilangan datamu.',
    },
    {
        icon: BookOpen,
        title: 'Lacak status baca',
        description:
            'Tandai buku sebagai ingin dibaca, sedang dibaca, sudah tamat, ditunda, atau berhenti. Rak tersusun otomatis.',
    },
    {
        icon: Star,
        title: 'Rating & review pribadi',
        description:
            'Beri bintang dan tulis sinopsis versi kamu sendiri supaya mudah diingat dan dibagikan.',
    },
    {
        icon: Languages,
        title: 'Filter bahasa buku',
        description:
            'Pisahkan koleksi Indonesia, Inggris, dan bahasa lain dengan satu klik di rak publikmu.',
    },
    {
        icon: Share2,
        title: 'Satu tautan untuk dibagikan',
        description:
            'Rakmu hidup di alamat yang mudah diingat, misalnya namamu, dan siap dibuka siapa pun.',
    },
    {
        icon: Users,
        title: 'Sosial media di profil',
        description:
            'Tempel username Instagram, TikTok, X, YouTube, LinkedIn, atau situs pribadimu di halaman rak.',
    },
];

const steps = [
    {
        title: 'Masuk dengan Google',
        description:
            'Satu klik untuk mulai, tanpa perlu mengingat kata sandi baru.',
    },
    {
        title: 'Pilih username rak',
        description:
            'Username ini jadi alamat rak publikmu dan bisa diubah di pengaturan.',
    },
    {
        title: 'Susun dan bagikan',
        description:
            'Tambahkan buku, tulis catatanmu, lalu bagikan tautan rak ke teman.',
    },
];

function coverOf(shelf: FeaturedShelf): string | null {
    return shelf.covers[0] ?? null;
}
</script>

<template>
    <div>
        <SeoHead
            title="Rak buku pribadi yang bisa dibagikan"
            :description="description"
            :canonical="canonical"
        />
        <JsonLd :data="structuredData" />

        <section class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-primary/10 via-background to-background"
                aria-hidden="true"
            />

            <div
                class="mx-auto grid w-full max-w-6xl items-center gap-12 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_0.95fr]"
            >
                <div class="space-y-6">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                    >
                        <Sparkles class="size-3.5" />
                        Rak buku pribadi yang bisa dibagikan
                    </span>

                    <h1
                        class="font-display text-4xl leading-[1.05] text-balance text-foreground sm:text-5xl lg:text-6xl"
                    >
                        Simpan buku yang sudah kamu baca dalam
                        <span class="text-primary">rak digital</span> milikmu
                        sendiri.
                    </h1>

                    <p
                        class="max-w-xl text-base leading-relaxed text-pretty text-muted-foreground sm:text-lg"
                    >
                        Catat judul, penulis, dan bahasanya. Beri rating, tulis
                        kesanmu, lalu bagikan satu tautan agar siapa pun bisa
                        melihat rak dan rekomendasimu.
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button as-child size="lg">
                            <Link :href="register()">
                                Mulai gratis
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>

                        <Button as-child size="lg" variant="outline">
                            <Link :href="explore()">
                                <Compass class="size-4" />
                                Lihat rak orang lain
                            </Link>
                        </Button>
                    </div>

                    <dl class="grid grid-cols-3 gap-4 pt-4">
                        <div>
                            <dd class="font-display text-2xl text-foreground">
                                {{ formatNumber(props.numbers.readers) }}
                            </dd>
                            <dt class="text-xs text-muted-foreground">
                                Pemilik rak
                            </dt>
                        </div>
                        <div>
                            <dd class="font-display text-2xl text-foreground">
                                {{ formatNumber(props.numbers.books) }}
                            </dd>
                            <dt class="text-xs text-muted-foreground">
                                Buku tercatat
                            </dt>
                        </div>
                        <div>
                            <dd class="font-display text-2xl text-foreground">
                                {{ formatNumber(props.numbers.finished) }}
                            </dd>
                            <dt class="text-xs text-muted-foreground">
                                Sudah tamat
                            </dt>
                        </div>
                    </dl>
                </div>

                <div class="space-y-4">
                    <div
                        class="rounded-3xl border border-border/70 bg-card/80 p-4 shadow-lg backdrop-blur sm:p-6"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <p class="font-display text-lg text-foreground">
                                Pratinjau rak
                            </p>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground"
                            >
                                <Heart
                                    class="size-3 fill-current text-rose-500"
                                />
                                5 gaya siap pakai
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <ShelfThemePreview
                                v-for="theme in props.themes"
                                :key="theme.value"
                                :theme="theme.value"
                            />
                        </div>
                    </div>

                    <p class="text-center text-xs text-muted-foreground">
                        Contoh di atas adalah gaya rak yang bisa kamu pilih
                        nanti.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
            <SectionHeading
                eyebrow="Kenapa Rak Buku"
                title="Semua yang dibutuhkan pembaca rapi dalam satu tempat"
                description="Dirancang untuk kebutuhan sehari-hari: cepat dicatat, mudah dicari, dan tetap enak dilihat saat dibagikan."
                align="center"
            />

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="space-y-3 rounded-2xl border border-border/70 bg-card p-5 shadow-xs transition-shadow hover:shadow-md"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <component :is="feature.icon" class="size-5" />
                    </span>
                    <h3 class="font-display text-lg text-foreground">
                        {{ feature.title }}
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </article>
            </div>
        </section>

        <section class="border-y border-border/70 bg-muted/30">
            <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
                <SectionHeading
                    eyebrow="Status baca"
                    title="Setiap buku tahu sedang ada di mana"
                    description="Rakmu tersusun sendiri mengikuti status, jadi kamu langsung tahu apa yang sedang dibaca dan apa yang sudah tamat."
                />

                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="status in props.statuses"
                        :key="status.value"
                        class="flex items-start gap-3 rounded-2xl border border-border/70 bg-card p-4"
                    >
                        <span
                            :class="
                                cn(
                                    'flex size-9 shrink-0 items-center justify-center rounded-xl',
                                    statusStyle(status.color).badge,
                                )
                            "
                        >
                            <component
                                :is="iconByName(status.icon)"
                                class="size-4"
                            />
                        </span>
                        <div class="space-y-0.5">
                            <p class="text-sm font-medium text-foreground">
                                {{ status.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ status.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
            <SectionHeading
                eyebrow="Cara kerja"
                title="Tiga langkah dan rakmu siap dibagikan"
                align="center"
            />

            <ol class="mt-10 grid gap-4 md:grid-cols-3">
                <li
                    v-for="(step, index) in steps"
                    :key="step.title"
                    class="relative space-y-2 rounded-2xl border border-border/70 bg-card p-5"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-full bg-primary font-display text-lg text-primary-foreground"
                    >
                        {{ index + 1 }}
                    </span>
                    <h3 class="font-display text-lg text-foreground">
                        {{ step.title }}
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ step.description }}
                    </p>
                </li>
            </ol>
        </section>

        <section
            v-if="props.featuredShelves.length > 0"
            class="border-y border-border/70 bg-muted/30"
        >
            <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <SectionHeading
                        eyebrow="Rak pilihan"
                        title="Intip rak yang sedang ramai"
                        description="Beberapa pembaca yang sudah membagikan raknya di sini."
                    />
                    <Button as-child variant="outline">
                        <Link :href="explore()">
                            Telusuri semua
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="shelf in props.featuredShelves"
                        :key="shelf.username"
                        class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 font-display text-lg text-primary"
                            >
                                <img
                                    v-if="shelf.avatar_url"
                                    :src="shelf.avatar_url"
                                    :alt="shelf.name"
                                    class="size-full object-cover"
                                />
                                <template v-else>
                                    {{ shelf.name.charAt(0).toUpperCase() }}
                                </template>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-foreground">
                                    {{ shelf.name }}
                                </p>
                                <p class="truncate text-xs text-primary">
                                    /{{ shelf.username }}
                                </p>
                            </div>
                        </div>

                        <div
                            :data-shelf-theme="shelf.theme"
                            :class="cn(shelf.theme === 'neon' && 'dark')"
                        >
                            <div class="flex items-end gap-2">
                                <div
                                    v-for="index in 4"
                                    :key="index"
                                    class="w-10 shrink-0"
                                >
                                    <img
                                        v-if="index === 1 && coverOf(shelf)"
                                        :src="coverOf(shelf)!"
                                        alt=""
                                        class="aspect-2/3 w-full rounded-[3px] object-cover shadow-sm"
                                    />
                                    <div
                                        v-else
                                        class="aspect-2/3 w-full rounded-[3px] shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                'var(--shelf-surface-2)',
                                            border: '1px solid var(--shelf-plank)',
                                        }"
                                    />
                                </div>
                            </div>
                            <div class="shelf-plank mt-2 h-2 rounded-full" />
                        </div>

                        <p
                            class="line-clamp-2 min-h-10 text-sm text-muted-foreground"
                        >
                            {{ shelf.tagline ?? 'Rak buku pribadi.' }}
                        </p>

                        <div
                            class="mt-auto flex items-center justify-between gap-2"
                        >
                            <span class="text-xs text-muted-foreground">
                                {{ formatNumber(shelf.books_count) }} buku ·
                                {{ shelf.theme_label }}
                            </span>
                            <Button as-child variant="ghost" size="sm">
                                <Link :href="shelfShow(shelf.username)">
                                    Kunjungi
                                </Link>
                            </Button>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
            <div
                class="grid gap-8 rounded-3xl border border-border/70 bg-card p-6 shadow-sm sm:p-10 lg:grid-cols-[1fr_auto] lg:items-center"
            >
                <div class="space-y-4">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent-foreground"
                    >
                        <Quote class="size-3.5" />
                        Gratis untuk pembaca
                    </span>
                    <h2
                        class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
                    >
                        Mulai rak pertamamu hari ini
                    </h2>
                    <p
                        class="max-w-xl text-sm leading-relaxed text-muted-foreground sm:text-base"
                    >
                        Tidak perlu perangkat khusus. Buat akun, susun raknya,
                        lalu bagikan tautanmu ke teman, komunitas, atau media
                        sosial favoritmu.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button as-child size="lg">
                        <Link :href="register()">
                            Buat rak sekarang
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button as-child size="lg" variant="ghost">
                        <Link :href="login()">Sudah punya akun</Link>
                    </Button>
                </div>
            </div>
        </section>
    </div>
</template>
