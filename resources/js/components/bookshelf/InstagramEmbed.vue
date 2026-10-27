<script setup lang="ts">
import { computed } from 'vue';
import { ExternalLink } from '@lucide/vue';

const props = defineProps<{
    url: string;
}>();

/**
 * Ubah URL post/reel IG menjadi URL embed tanpa API.
 * Mendukung /p/, /reel/, /reels/ dengan atau tanpa www dan query string.
 * Contoh: https://www.instagram.com/p/ABC123/?igsh=... -> .../p/ABC123/embed
 */
const embedSrc = computed<string | null>(() => {
    try {
        const parsed = new URL(
            /^https?:\/\//i.test(props.url.trim())
                ? props.url.trim()
                : `https://${props.url.trim()}`,
        );

        if (!/(^|\.)instagram\.com$/i.test(parsed.hostname)) {
            return null;
        }

        const match = parsed.pathname.match(
            /\/(p|reel|reels)\/([A-Za-z0-9_-]+)/,
        );

        if (!match) {
            return null;
        }

        const [, type, shortcode] = match;
        const kind = type === 'p' ? 'p' : 'reel';

        return `https://www.instagram.com/${kind}/${shortcode}/embed`;
    } catch {
        return null;
    }
});
</script>

<template>
    <figure v-if="embedSrc" class="space-y-2">
        <div
            class="w-full max-w-[240px] overflow-hidden rounded-xl border border-border/70 bg-muted/30 sm:max-w-[360px]"
        >
            <iframe
                :src="embedSrc"
                title="Embed post Instagram"
                loading="lazy"
                allowtransparency="true"
                frameborder="0"
                scrolling="no"
                class="h-[420px] max-h-[60vh] w-full sm:h-[540px] sm:max-h-[70vh]"
            />
        </div>
        <figcaption>
            <a
                :href="props.url"
                target="_blank"
                rel="noopener noreferrer ugc"
                class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-primary hover:underline"
            >
                Buka di Instagram
                <ExternalLink class="size-3" />
            </a>
        </figcaption>
    </figure>
</template>
