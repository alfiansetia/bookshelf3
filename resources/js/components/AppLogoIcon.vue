<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- Mark Rak Buku: empat buku di atas rak, satu buku miring. -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="86 146 327 254"
        fill="currentColor"
        :class="className"
        v-bind="$attrs"
    >
        <rect x="108" y="170" width="56" height="186" rx="18" />
        <rect x="180" y="224" width="56" height="132" rx="18" />
        <rect x="252" y="196" width="56" height="160" rx="18" />
        <rect
            x="316"
            y="150"
            width="56"
            height="206"
            rx="18"
            transform="rotate(11 344 356)"
        />
        <rect x="88" y="356" width="336" height="40" rx="20" />
    </svg>
</template>
