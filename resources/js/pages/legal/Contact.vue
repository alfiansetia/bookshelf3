<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, CodeXml, Mail, MailOpen } from '@lucide/vue';
import SeoHead from '@/components/seo/SeoHead.vue';
import SocialIcon from '@/components/bookshelf/SocialIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

interface ContactSocial {
    platform: string;
    label: string;
    handle: string;
    url: string;
}

const props = defineProps<{
    canonical: string;
    email: string;
    socials: ContactSocial[];
}>();

function socialIcon(platform: string) {
    if (platform === 'github') {
        return CodeXml;
    }

    return null;
}

const usesBrandIcon = (platform: string): boolean =>
    platform === 'instagram' || platform === 'tiktok';
</script>

<template>
    <SeoHead
        title="Kontak"
        description="Hubungi pengelola Rak Buku lewat email, TikTok, Instagram, atau GitHub."
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
                <MailOpen class="size-3.5" />
                Kontak
            </span>

            <h1
                class="font-display text-3xl leading-tight text-balance text-foreground sm:text-4xl"
            >
                Butuh bantuan? Sapa kami saja
            </h1>

            <p class="text-base leading-relaxed text-muted-foreground">
                Untuk pertanyaan soal akun, data, laporan penyalahgunaan, atau
                sekadar saran fitur, email adalah jalur tercepat. Media sosial
                cocok untuk kabar dan obrolan santai.
            </p>
        </header>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">Lewat email</h2>
            <div
                class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <Mail class="size-5" />
                    </span>
                    <div>
                        <p class="font-medium text-foreground">{{ props.email }}</p>
                        <p class="text-sm text-muted-foreground">
                            Sertakan alamat email akun dan username rakmu agar
                            mudah ditelusuri.
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <a :href="`mailto:${props.email}`">Kirim email</a>
                </Button>
            </div>
        </section>

        <section class="mt-10 space-y-4">
            <h2 class="font-display text-xl text-foreground">Lewat media sosial</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <a
                    v-for="social in props.socials"
                    :key="social.platform"
                    :href="social.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group space-y-3 rounded-2xl border border-border/70 bg-card p-5 shadow-xs transition-shadow hover:shadow-md"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <SocialIcon
                            v-if="usesBrandIcon(social.platform)"
                            :platform="social.platform"
                            class="size-5"
                        />
                        <component
                            v-else
                            :is="socialIcon(social.platform)"
                            class="size-5"
                        />
                    </span>
                    <div>
                        <p class="font-medium text-foreground">{{ social.label }}</p>
                        <p class="truncate text-sm text-muted-foreground">
                            {{ social.handle }}
                        </p>
                    </div>
                    <p class="text-sm font-medium text-primary group-hover:underline group-hover:underline-offset-4">
                        Kunjungi profil
                    </p>
                </a>
            </div>
        </section>

        <section
            class="mt-10 space-y-2 rounded-2xl border border-border/70 bg-muted/40 p-6 text-sm leading-relaxed text-muted-foreground"
        >
            <h2 class="font-display text-xl text-foreground">Sebelum menghubungi</h2>
            <p>
                Cek dulu halaman FAQ dan kebijakan privasi — sebagian besar
                pertanyaan soal rak publik, privasi, dan hapus akun sudah
                dijawab di sana.
            </p>
        </section>
    </div>
</template>
