<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useClipboard } from '@vueuse/core';
import { Check, Link2, Share2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        url: string;
        title?: string;
        text?: string;
        label?: string;
        size?: 'sm' | 'default' | 'lg';
        variant?: 'default' | 'outline' | 'secondary' | 'ghost';
        class?: string;
    }>(),
    {
        title: undefined,
        text: undefined,
        label: 'Bagikan rak',
        size: 'default',
        variant: 'outline',
    },
);

const { copy } = useClipboard({ legacy: true });
const copied = ref(false);
// Dihitung setelah mount supaya SSR dan client merender ikon yang sama.
// `navigator.share` hanya ada di browser, jadi menilainya saat render
// server membuat hydration mismatch (server: Link2, client: Share2).
const canShare = ref(false);

onMounted(() => {
    canShare.value = 'share' in navigator;
});

async function share(): Promise<void> {
    if (canShare.value) {
        try {
            await navigator.share({
                url: props.url,
                title: props.title,
                text: props.text,
            });

            return;
        } catch {
            // Pengguna menutup dialog berbagi, lanjut ke fallback salin tautan.
        }
    }

    copy(props.url);
    copied.value = true;

    window.setTimeout(() => {
        copied.value = false;
    }, 2000);
}
</script>

<template>
    <Button
        type="button"
        :size="props.size"
        :variant="props.variant"
        :class="cn(props.class)"
        @click="share"
    >
        <Check v-if="copied" class="size-4 text-emerald-600" />
        <Share2 v-else-if="canShare" class="size-4" />
        <Link2 v-else class="size-4" />

        <span>{{ copied ? 'Tautan tersalin!' : props.label }}</span>
    </Button>
</template>
