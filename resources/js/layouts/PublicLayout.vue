<script setup lang="ts">
import CookieConsent from '@/components/CookieConsent.vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-svh flex-col bg-background">
        <PublicHeader />

        <main class="flex-1">
            <slot />
        </main>

        <PublicFooter />
        <Toaster />
        <CookieConsent />
    </div>
</template>
