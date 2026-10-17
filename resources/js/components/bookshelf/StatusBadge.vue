<script setup lang="ts">
import { computed } from 'vue';
import { iconByName } from '@/lib/icons';
import { statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import type { BookStatus, StatusColor, StatusOption } from '@/types';

const props = withDefaults(
    defineProps<{
        status: BookStatus;
        label?: string | null;
        color?: StatusColor | null;
        icon?: string | null;
        statuses?: StatusOption[];
        size?: 'sm' | 'md';
        class?: string;
    }>(),
    {
        label: null,
        color: null,
        icon: null,
        statuses: () => [],
        size: 'sm',
    },
);

const option = computed(() =>
    props.statuses.find((item) => item.value === props.status),
);

const color = computed<StatusColor>(
    () => props.color ?? option.value?.color ?? 'sky',
);

const label = computed(
    () => props.label ?? option.value?.label ?? props.status,
);

const style = computed(() => statusStyle(color.value));
const icon = computed(() => iconByName(props.icon ?? option.value?.icon));
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1.5 rounded-full font-medium ring-1 ring-inset',
                props.size === 'sm'
                    ? 'px-2 py-0.5 text-[11px]'
                    : 'px-3 py-1 text-xs',
                style.badge,
                props.class,
            )
        "
    >
        <component
            :is="icon"
            :class="props.size === 'sm' ? 'size-3' : 'size-3.5'"
        />
        {{ label }}
    </span>
</template>
