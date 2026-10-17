<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SocialIcon from '@/components/bookshelf/SocialIcon.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import { redirect as googleRedirect } from '@/routes/auth/google';

defineOptions({
    layout: {
        title: 'Masuk ke rak bukumu',
        description: 'Cukup satu klik pakai akun Google, tanpa kata sandi.',
    },
});
</script>

<template>
    <Head title="Masuk" />

    <div class="flex flex-col gap-6">
        <Button as-child size="lg" class="w-full">
            <a :href="googleRedirect.url()" data-test="google-login-button">
                <span class="mr-2 flex size-5 items-center justify-center">
                    <SocialIcon platform="google" class="size-5" />
                </span>
                Lanjutkan dengan Google
            </a>
        </Button>

        <p class="text-center text-sm text-muted-foreground">
            Rak Buku tidak menyimpan kata sandi. Akun dan keamananmu dipegang
            penuh oleh Google.
        </p>

        <div class="text-center text-sm text-muted-foreground">
            Belum punya akun?
            <TextLink :href="register()" data-test="register-link">
                Daftar
            </TextLink>
        </div>
    </div>
</template>
