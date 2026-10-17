<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        hint?: string;
        icon?: LucideIcon;
        tone?: 'default' | 'primary' | 'accent';
        class?: string;
    }>(),
    {
        hint: undefined,
        icon: undefined,
        tone: 'default',
    },
);

const tones = {
    default: 'bg-card text-foreground',
    primary: 'bg-primary text-primary-foreground',
    accent: 'bg-accent text-accent-foreground',
} as const;

const iconTones = {
    default: 'bg-muted text-muted-foreground',
    primary: 'bg-white/15 text-primary-foreground',
    accent: 'bg-black/10 text-accent-foreground',
} as const;
</script>

<template>
    <div
        :class="
            cn(
                'flex items-start gap-3 rounded-2xl border border-border/70 p-4 shadow-xs',
                tones[props.tone],
                props.class,
            )
        "
    >
        <span
            v-if="props.icon"
            :class="
                cn(
                    'flex size-9 shrink-0 items-center justify-center rounded-xl',
                    iconTones[props.tone],
                )
            "
        >
            <component :is="props.icon" class="size-4.5" />
        </span>

        <div class="min-w-0 space-y-0.5">
            <p
                class="truncate font-display text-2xl leading-none"
                :class="
                    props.tone === 'default'
                        ? 'text-foreground'
                        : 'text-current'
                "
            >
                {{ props.value }}
            </p>
            <p
                class="text-xs font-medium tracking-wide uppercase"
                :class="
                    props.tone === 'default'
                        ? 'text-muted-foreground'
                        : 'opacity-80'
                "
            >
                {{ props.label }}
            </p>
            <p v-if="props.hint" class="text-xs opacity-70">
                {{ props.hint }}
            </p>
        </div>
    </div>
</template>
