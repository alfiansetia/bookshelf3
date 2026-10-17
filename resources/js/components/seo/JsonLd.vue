<script setup lang="ts">
import { computed } from 'vue';

/**
 * Menyisipkan data terstruktur schema.org (JSON-LD).
 *
 * Dipasang di dalam <body>, bukan lewat <Head>, karena isi <script> harus
 * dikirim mentah. Dengan `innerHTML` hasilnya tidak di-escape saat SSR
 * sedangkan di peramban isi itu tetap tersimpan sebagai teks skrip.
 */
const props = defineProps<{
    data: Record<string, unknown> | Record<string, unknown>[];
}>();

const attributes = computed(() => ({
    type: 'application/ld+json',
    innerHTML: JSON.stringify(props.data),
}));
</script>

<template>
    <component :is="'script'" v-bind="attributes" />
</template>
