<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookOpen, Palette, Share2 } from '@lucide/vue';
import SocialIcon from '@/components/bookshelf/SocialIcon.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';
import { redirect as googleRedirect } from '@/routes/auth/google';

defineOptions({
    layout: {
        title: 'Buat rak bukumu',
        description: 'Daftar hanya butuh akun Google — tanpa kata sandi.',
    },
});

const perks = [
    {
        icon: BookOpen,
        text: 'Catat buku yang sudah, sedang, dan ingin kamu baca.',
    },
    {
        icon: Palette,
        text: 'Pilih satu dari empat tema rak sesuai seleramu.',
    },
    {
        icon: Share2,
        text: 'Dapat alamat rak sendiri untuk dibagikan ke siapa saja.',
    },
];
</script>

<template>
    <Head title="Daftar" />

    <div class="flex flex-col gap-6">
        <Button as-child size="lg" class="w-full">
            <a :href="googleRedirect.url()" data-test="google-register-button">
                <span class="mr-2 flex size-5 items-center justify-center">
                    <SocialIcon platform="google" class="size-5" />
                </span>
                Daftar dengan Google
            </a>
        </Button>

        <ul class="flex flex-col gap-3">
            <li
                v-for="perk in perks"
                :key="perk.text"
                class="flex items-start gap-3 text-sm text-muted-foreground"
            >
                <component
                    :is="perk.icon"
                    class="mt-0.5 size-4 shrink-0 text-primary"
                />
                <span>{{ perk.text }}</span>
            </li>
        </ul>

        <p class="text-center text-sm text-muted-foreground">
            Dengan mendaftar kamu setuju bahwa Rak Buku tidak menyimpan kata
            sandi apa pun — seluruh proses masuk ditangani Google.
        </p>

        <div class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink :href="login()" data-test="login-link">Masuk</TextLink>
        </div>
    </div>
</template>
