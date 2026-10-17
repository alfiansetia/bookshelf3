<script setup lang="ts">
import { computed, ref } from 'vue';
import { Star, X } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: number | null;
        max?: number;
        class?: string;
    }>(),
    {
        modelValue: null,
        max: 5,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
}>();

const hovered = ref<number | null>(null);

const active = computed(() => hovered.value ?? props.modelValue ?? 0);

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
            aria-label="Beri rating bintang"
            @mouseleave="hovered = null"
        >
            <button
                v-for="star in props.max"
                :key="star"
                type="button"
                role="radio"
                :aria-checked="props.modelValue === star"
                :aria-label="`Beri ${star} bintang`"
                class="rounded-md p-1 transition-transform hover:scale-110 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                @mouseenter="hovered = star"
                @focus="hovered = star"
                @blur="hovered = null"
                @click="select(star)"
            >
                <Star
                    :class="
                        cn(
                            'size-6 transition-colors',
                            active >= star
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-neutral-300 dark:text-neutral-600',
                        )
                    "
                    :stroke-width="1.5"
                />
            </button>
        </div>

        <span class="text-sm text-muted-foreground">
            {{
                props.modelValue
                    ? `${props.modelValue} dari ${props.max} bintang`
                    : 'Belum dinilai'
            }}
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
    </div>
</template>
