<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { cn } from '@/lib/utils';
import { coverGradient, titleInitials } from '@/lib/format';
import type { Book } from '@/types';

type Size = 'xs' | 'sm' | 'md' | 'lg' | 'xl';

const props = withDefaults(
    defineProps<{
        book?: Book | null;
        title?: string;
        author?: string | null;
        coverUrl?: string | null;
        size?: Size;
        /** Tampilkan punggung buku di sisi kiri supaya terlihat seperti buku fisik. */
        spine?: boolean;
        class?: string;
    }>(),
    {
        book: null,
        title: undefined,
        author: undefined,
        coverUrl: undefined,
        size: 'md',
        spine: true,
    },
);

const sizes: Record<Size, string> = {
    xs: 'w-10 rounded-[3px] text-[7px]',
    sm: 'w-16 rounded-[4px] text-[9px]',
    md: 'w-24 rounded-[6px] text-[11px]',
    lg: 'w-32 rounded-md text-sm',
    xl: 'w-44 rounded-lg text-base',
};

const imageFailed = ref(false);

const resolvedTitle = computed(
    () => props.title ?? props.book?.title ?? 'Tanpa judul',
);
const resolvedAuthor = computed(
    () => props.author ?? props.book?.author ?? null,
);
const resolvedCover = computed(
    () => props.coverUrl ?? props.book?.cover_url ?? null,
);

const showImage = computed(
    () => Boolean(resolvedCover.value) && !imageFailed.value,
);
const gradient = computed(() => coverGradient(resolvedTitle.value));
const initials = computed(() => titleInitials(resolvedTitle.value));

watch(resolvedCover, () => {
    imageFailed.value = false;
});
</script>

<template>
    <div
        :class="
            cn(
                'relative aspect-2/3 shrink-0 overflow-hidden border border-black/10 bg-neutral-200 shadow-sm dark:border-white/10 dark:bg-neutral-800',
                sizes[props.size],
                props.class,
            )
        "
    >
        <img
            v-if="showImage"
            :src="resolvedCover!"
            :alt="`Sampul ${resolvedTitle}`"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-cover"
            @error="imageFailed = true"
        />

        <div
            v-else
            :class="
                cn(
                    'flex h-full w-full flex-col justify-between bg-gradient-to-br p-1.5 text-white',
                    gradient,
                )
            "
        >
            <span class="font-display leading-none opacity-70">
                {{ initials }}
            </span>
            <span class="line-clamp-3 leading-[1.15] font-semibold">
                {{ resolvedTitle }}
            </span>
            <span
                v-if="resolvedAuthor"
                class="truncate text-[0.7em] leading-none opacity-80"
            >
                {{ resolvedAuthor }}
            </span>
        </div>

        <span
            v-if="props.spine"
            class="pointer-events-none absolute inset-y-0 left-0 w-[7%] bg-gradient-to-r from-black/35 via-black/10 to-transparent"
            aria-hidden="true"
        />
    </div>
</template>
