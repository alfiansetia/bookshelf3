<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        eyebrow?: string;
        title: string;
        description?: string;
        align?: 'left' | 'center';
        class?: string;
    }>(),
    {
        eyebrow: undefined,
        description: undefined,
        align: 'left',
    },
);
</script>

<template>
    <div
        :class="
            cn(
                'space-y-3',
                props.align === 'center' && 'mx-auto max-w-2xl text-center',
                props.class,
            )
        "
    >
        <p
            v-if="props.eyebrow"
            class="text-xs font-semibold tracking-[0.18em] text-primary uppercase"
        >
            {{ props.eyebrow }}
        </p>

        <h2
            class="font-display text-2xl leading-tight text-foreground sm:text-3xl md:text-4xl"
        >
            {{ props.title }}
        </h2>

        <p
            v-if="props.description"
            :class="
                cn(
                    'text-sm leading-relaxed text-muted-foreground sm:text-base',
                    props.align === 'center' && 'text-balance',
                )
            "
        >
            {{ props.description }}
        </p>

        <div v-if="$slots.default" class="pt-1">
            <slot />
        </div>
    </div>
</template>
