<script setup lang="ts">
import { cn } from '@/lib/utils';
import SocialIcon from '@/components/bookshelf/SocialIcon.vue';
import type { SocialProfile } from '@/types';

const props = withDefaults(
    defineProps<{
        links: SocialProfile[];
        showLabels?: boolean;
        class?: string;
    }>(),
    {
        showLabels: false,
    },
);
</script>

<template>
    <ul
        v-if="props.links.length > 0"
        :class="cn('flex flex-wrap items-center gap-2', props.class)"
    >
        <li v-for="link in props.links" :key="link.platform">
            <a
                :href="link.url"
                target="_blank"
                rel="me noopener noreferrer"
                :aria-label="`${link.label} ${link.handle}`"
                :class="
                    cn(
                        'inline-flex items-center gap-2 rounded-full border border-border/70 bg-card/80 px-3 py-1.5 text-sm text-foreground shadow-xs transition-colors hover:border-primary/40 hover:bg-primary/5 hover:text-primary',
                    )
                "
            >
                <SocialIcon :platform="link.platform" class="size-4" />
                <span :class="props.showLabels ? '' : 'sr-only'">
                    {{ link.label }}
                </span>
                <span v-if="props.showLabels" class="text-muted-foreground">
                    · {{ link.handle }}
                </span>
            </a>
        </li>
    </ul>
</template>
