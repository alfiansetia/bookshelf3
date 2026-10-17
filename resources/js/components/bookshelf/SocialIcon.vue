<script setup lang="ts">
import { Globe } from '@lucide/vue';

/**
 * Lencana merek untuk tiap platform sosial.
 *
 * Ikon digambar langsung sebagai SVG agar tidak bergantung pada paket ikon
 * pihak ketiga (Lucide sudah tidak lagi menyediakan ikon merek).
 */
const props = withDefaults(
    defineProps<{
        platform: string;
        class?: string;
    }>(),
    {
        class: 'size-4',
    },
);
</script>

<template>
    <svg
        v-if="props.platform === 'instagram'"
        :class="props.class"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        aria-hidden="true"
    >
        <rect x="3" y="3" width="18" height="18" rx="5" />
        <circle cx="12" cy="12" r="4" />
        <circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none" />
    </svg>

    <svg
        v-else-if="props.platform === 'x'"
        :class="props.class"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        <path
            d="M17.53 3h3.02l-6.6 7.54L21.75 21h-5.8l-4.55-5.96L6.2 21H3.18l7.06-8.07L2.5 3h5.95l4.11 5.44L17.53 3Zm-1.06 16.2h1.67L7.6 4.71H5.81l10.66 14.49Z"
        />
    </svg>

    <svg
        v-else-if="props.platform === 'youtube'"
        :class="props.class"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        <path
            d="M21.6 7.2a2.8 2.8 0 0 0-2-2C17.9 4.8 12 4.8 12 4.8s-5.9 0-7.6.4a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2 12a29 29 0 0 0 .4 4.8 2.8 2.8 0 0 0 2 2c1.7.4 7.6.4 7.6.4s5.9 0 7.6-.4a2.8 2.8 0 0 0 2-2A29 29 0 0 0 22 12a29 29 0 0 0-.4-4.8ZM10 15.5v-7l6 3.5-6 3.5Z"
        />
    </svg>

    <svg
        v-else-if="props.platform === 'linkedin'"
        :class="props.class"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        <path
            d="M4.98 3.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05c.53-.95 1.83-1.95 3.77-1.95C21.3 8.75 22 11 22 14.2V21h-4v-6c0-1.4-.5-2.4-1.8-2.4-1 0-1.6.7-1.85 1.35-.1.24-.12.57-.12.9V21h-4V9Z"
        />
    </svg>

    <svg
        v-else-if="props.platform === 'tiktok'"
        :class="props.class"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        <path
            d="M16 3h-3v12.2a2.6 2.6 0 1 1-2.6-2.6c.24 0 .47.03.7.1V9.6a5.8 5.8 0 1 0 4.9 5.7V9.3c1 .8 2.3 1.3 3.7 1.3V7.4A3.7 3.7 0 0 1 16 3Z"
        />
    </svg>

    <svg
        v-else-if="props.platform === 'google'"
        :class="props.class"
        viewBox="0 0 24 24"
        aria-hidden="true"
    >
        <path
            fill="#4285F4"
            d="M23.52 12.27c0-.85-.08-1.67-.22-2.45H12v4.63h6.46a5.52 5.52 0 0 1-2.4 3.62v3h3.88c2.27-2.09 3.58-5.17 3.58-8.8Z"
        />
        <path
            fill="#34A853"
            d="M12 24c3.24 0 5.96-1.08 7.94-2.91l-3.88-3.01c-1.08.72-2.45 1.15-4.06 1.15-3.13 0-5.78-2.11-6.72-4.96H1.29v3.09A12 12 0 0 0 12 24Z"
        />
        <path
            fill="#FBBC05"
            d="M5.28 14.27a7.2 7.2 0 0 1 0-4.54V6.64H1.29a12 12 0 0 0 0 10.72l3.99-3.09Z"
        />
        <path
            fill="#EA4335"
            d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.18 15.24 0 12 0A12 12 0 0 0 1.29 6.64l3.99 3.09C6.22 6.89 8.87 4.75 12 4.75Z"
        />
    </svg>

    <Globe v-else :class="props.class" aria-hidden="true" />
</template>
