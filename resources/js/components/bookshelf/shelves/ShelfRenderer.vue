<script setup lang="ts">
import { computed } from 'vue';
import { shelfThemeComponent } from '@/lib/shelfThemes';
import { cn } from '@/lib/utils';
import type { ShelfRow, ShelfTheme } from '@/types';

const props = withDefaults(
    defineProps<{
        theme: ShelfTheme;
        rows: ShelfRow[];
        owner?: boolean;
        class?: string;
    }>(),
    {
        owner: false,
    },
);

const component = computed(() => shelfThemeComponent(props.theme));

/**
 * Tema neon selalu tampil gelap, apa pun preferensi pengunjung.
 */
const forceDark = computed(() => props.theme === 'neon');
</script>

<template>
    <div
        :data-shelf-theme="props.theme"
        :class="
            cn(
                'shelf-surface rounded-3xl border border-black/5 p-4 shadow-sm sm:p-8 dark:border-white/5',
                forceDark && 'dark',
                props.class,
            )
        "
    >
        <component
            :is="component"
            v-if="props.rows.length > 0"
            :rows="props.rows"
            :owner="props.owner"
        />

        <slot v-else name="empty" />
    </div>
</template>
