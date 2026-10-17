<script setup lang="ts">
import { Check } from '@lucide/vue';
import ShelfThemePreview from '@/components/bookshelf/ShelfThemePreview.vue';
import { cn } from '@/lib/utils';
import type { ShelfTheme, ShelfThemeOption } from '@/types';

const props = withDefaults(
    defineProps<{
        themes: ShelfThemeOption[];
        modelValue: ShelfTheme;
        class?: string;
    }>(),
    {},
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: ShelfTheme): void;
}>();
</script>

<template>
    <div
        :class="cn('grid gap-4 sm:grid-cols-2', props.class)"
        role="radiogroup"
        aria-label="Pilih gaya rak buku"
    >
        <button
            v-for="theme in props.themes"
            :key="theme.value"
            type="button"
            role="radio"
            :aria-checked="props.modelValue === theme.value"
            :class="
                cn(
                    'group relative flex flex-col gap-3 rounded-2xl border-2 p-4 text-left transition-all',
                    props.modelValue === theme.value
                        ? 'border-primary bg-primary/5 shadow-sm'
                        : 'border-border/70 bg-card hover:border-primary/40 hover:bg-primary/5',
                )
            "
            @click="emit('update:modelValue', theme.value)"
        >
            <span
                v-if="props.modelValue === theme.value"
                class="absolute top-3 right-3 flex size-6 items-center justify-center rounded-full bg-primary text-primary-foreground"
            >
                <Check class="size-3.5" />
            </span>

            <ShelfThemePreview :theme="theme.value" />

            <div class="space-y-1">
                <p class="font-display text-lg leading-tight text-foreground">
                    {{ theme.label }}
                </p>
                <p class="text-xs leading-relaxed text-muted-foreground">
                    {{ theme.tagline }}
                </p>
            </div>
        </button>
    </div>
</template>
