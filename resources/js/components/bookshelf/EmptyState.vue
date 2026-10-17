<script setup lang="ts">
import { Inbox } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        icon?: LucideIcon;
        title: string;
        description?: string;
        class?: string;
    }>(),
    {
        icon: undefined,
        description: undefined,
    },
);

const iconComponent = props.icon ?? Inbox;
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-border/80 bg-card/60 px-6 py-12 text-center',
                props.class,
            )
        "
    >
        <span
            class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground"
        >
            <component :is="iconComponent" class="size-6" />
        </span>

        <div class="space-y-1">
            <p class="font-display text-lg text-foreground">
                {{ props.title }}
            </p>
            <p
                v-if="props.description"
                class="mx-auto max-w-md text-sm text-balance text-muted-foreground"
            >
                {{ props.description }}
            </p>
        </div>

        <div
            v-if="$slots.default"
            class="mt-1 flex flex-wrap items-center justify-center gap-2"
        >
            <slot />
        </div>
    </div>
</template>
