<script setup lang="ts">
import { computed } from 'vue';
import { Star } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { ratingLabel } from '@/lib/format';

const props = withDefaults(
    defineProps<{
        value?: number | null;
        max?: number;
        size?: 'sm' | 'md' | 'lg';
        showValue?: boolean;
        class?: string;
    }>(),
    {
        value: null,
        max: 5,
        size: 'md',
        showValue: false,
    },
);

const starSizes = {
    sm: 'size-3.5',
    md: 'size-4',
    lg: 'size-6',
} as const;

const rating = computed(() => props.value ?? 0);

const stars = computed(() =>
    Array.from({ length: props.max }, (_, index) => {
        const position = index + 1;

        if (rating.value >= position) {
            return 1;
        }

        if (rating.value >= position - 0.5) {
            return 0.5;
        }

        return 0;
    }),
);
</script>

<template>
    <div
        :class="cn('flex items-center gap-1.5', props.class)"
        :title="ratingLabel(props.value)"
    >
        <div class="flex items-center gap-0.5" aria-hidden="true">
            <span
                v-for="(fill, index) in stars"
                :key="index"
                class="relative inline-flex"
            >
                <Star
                    :class="
                        cn(
                            starSizes[props.size],
                            fill === 0
                                ? 'text-neutral-300 dark:text-neutral-600'
                                : 'text-transparent',
                        )
                    "
                    :stroke-width="1.5"
                />
                <span
                    v-if="fill > 0"
                    class="absolute inset-0 overflow-hidden"
                    :style="{ width: fill === 1 ? '100%' : '50%' }"
                >
                    <Star
                        :class="
                            cn(
                                starSizes[props.size],
                                'fill-amber-400 text-amber-400',
                            )
                        "
                        :stroke-width="1.5"
                    />
                </span>
            </span>
        </div>

        <span v-if="props.showValue" class="text-xs text-muted-foreground">
            {{ props.value ? `${props.value}/5` : 'Belum dinilai' }}
        </span>
    </div>
</template>
