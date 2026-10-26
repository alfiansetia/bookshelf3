<script setup lang="ts">
import { computed, ref } from 'vue';
import { Star, X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { formatNumber } from '@/lib/format';

const props = withDefaults(
    defineProps<{
        modelValue?: number | null;
        max?: number;
        step?: number;
        class?: string;
    }>(),
    {
        modelValue: null,
        max: 5,
        step: 0.5,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
}>();

const hovered = ref<number | null>(null);

const active = computed(() => hovered.value ?? props.modelValue ?? 0);

const options = computed(() => {
    const values: number[] = [];
    for (let v = props.step; v <= props.max + 1e-9; v += props.step) {
        values.push(Math.round(v * 10) / 10);
    }
    return values;
});

function displayLabel(value: number | null): string {
    if (value === null) {
        return 'Belum dinilai';
    }

    return `${formatNumber(value)} dari ${props.max} bintang`;
}

function ariaLabel(value: number): string {
    return `Beri ${formatNumber(value)} bintang`;
}

function fillFor(position: number): 0 | 0.5 | 1 {
    if (active.value >= position) {
        return 1;
    }

    if (active.value >= position - 0.5) {
        return 0.5;
    }

    return 0;
}

function select(value: number): void {
    emit('update:modelValue', props.modelValue === value ? null : value);
}

function reset(): void {
    emit('update:modelValue', null);
}
</script>

<template>
    <div :class="cn('flex flex-wrap items-center gap-2', props.class)">
        <div
            class="flex items-center gap-1"
            role="radiogroup"
            aria-label="Beri rating bintang, bisa setengah"
            @mouseleave="hovered = null"
        >
            <span
                v-for="star in props.max"
                :key="star"
                class="relative inline-flex rounded-md p-1 focus-within:ring-2 focus-within:ring-ring focus-within:outline-none"
            >
                <Star
                    :class="
                        cn(
                            'size-6 transition-colors',
                            fillFor(star) === 0
                                ? 'text-neutral-300 dark:text-neutral-600'
                                : 'text-transparent',
                        )
                    "
                    :stroke-width="1.5"
                    aria-hidden="true"
                />
                <span
                    v-if="fillFor(star) > 0"
                    class="pointer-events-none absolute top-1 left-1 overflow-hidden"
                    :style="{
                        width:
                            fillFor(star) === 1
                                ? 'calc(100% - 0.5rem)'
                                : 'calc(50% - 0.25rem)',
                        height: 'calc(100% - 0.5rem)',
                    }"
                    aria-hidden="true"
                >
                    <Star
                        class="size-6 fill-amber-400 text-amber-400"
                        :stroke-width="1.5"
                    />
                </span>
                <button
                    v-for="value in [star - 0.5, star]"
                    :key="value"
                    type="button"
                    role="radio"
                    :aria-checked="props.modelValue === value"
                    :aria-label="ariaLabel(value)"
                    :title="ariaLabel(value)"
                    class="absolute inset-y-0 rounded-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="value % 1 === 0 ? 'right-0 w-1/2' : 'left-0 w-1/2'"
                    @mouseenter="hovered = value"
                    @focus="hovered = value"
                    @blur="hovered = null"
                    @click="select(value)"
                >
                    <span class="sr-only">{{ ariaLabel(value) }}</span>
                </button>
            </span>
        </div>

        <span class="text-sm text-muted-foreground">
            {{ displayLabel(props.modelValue ?? null) }}
        </span>

        <button
            v-if="props.modelValue"
            type="button"
            class="inline-flex items-center gap-1 text-xs text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
            @click="reset"
        >
            <X class="size-3" />
            Hapus rating
        </button>

        <span class="sr-only" aria-live="polite">
            {{ options.length }} pilihan rating tersedia
        </span>
    </div>
</template>
