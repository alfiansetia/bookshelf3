<script setup lang="ts">
import { computed } from 'vue';
import { shelfThemeComponent } from '@/lib/shelfThemes';
import { cn } from '@/lib/utils';
import type { BookStatus, ShelfBook, ShelfTheme } from '@/types';

const props = withDefaults(
    defineProps<{
        theme: ShelfTheme;
        books: ShelfBook[];
        spotlight?: BookStatus | null;
        favoriteSpotlight?: boolean;
        owner?: boolean;
        class?: string;
    }>(),
    {
        spotlight: null,
        favoriteSpotlight: false,
        owner: false,
    },
);

const component = computed(() => shelfThemeComponent(props.theme));

/**
 * Tema neon dan story selalu tampil gelap, apa pun preferensi pengunjung.
 */
const forceDark = computed(
    () => props.theme === 'neon' || props.theme === 'story',
);
</script>

<template>
    <div
        :data-shelf-theme="props.theme"
        :class="
            cn(
                'shelf-surface rounded-3xl border border-black/5 p-4 shadow-xs sm:p-8 dark:border-white/5',
                forceDark && 'dark',
                props.class,
            )
        "
    >
        <component
            :is="component"
            v-if="props.books.length > 0"
            :books="props.books"
            :spotlight="props.spotlight"
            :favorite-spotlight="props.favoriteSpotlight"
            :owner="props.owner"
        />

        <slot v-else name="empty" />
    </div>
</template>
