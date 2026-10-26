<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { ShelfTheme } from '@/types';

const props = withDefaults(
    defineProps<{
        theme: ShelfTheme;
        class?: string;
    }>(),
    {},
);

/**
 * Warna tiruan sampul buku pada tiap gaya rak.
 */
const covers: Record<ShelfTheme, string[]> = {
    wood: [
        'bg-gradient-to-br from-amber-600 to-amber-900',
        'bg-gradient-to-br from-stone-600 to-stone-900',
        'bg-gradient-to-br from-orange-500 to-red-900',
        'bg-gradient-to-br from-yellow-700 to-amber-900',
    ],
    minimal: [
        'bg-gradient-to-b from-neutral-200 to-neutral-400 dark:from-neutral-700 dark:to-neutral-800',
        'bg-gradient-to-b from-neutral-300 to-neutral-500 dark:from-neutral-600 dark:to-neutral-700',
        'bg-gradient-to-b from-neutral-100 to-neutral-300 dark:from-neutral-800 dark:to-neutral-700',
    ],
    neon: [
        'bg-gradient-to-br from-cyan-500/80 to-indigo-900',
        'bg-gradient-to-br from-fuchsia-500/80 to-slate-900',
        'bg-gradient-to-br from-emerald-400/80 to-slate-900',
    ],
    cozy: [
        'bg-gradient-to-br from-rose-300 to-rose-500',
        'bg-gradient-to-br from-amber-200 to-amber-400',
    ],
    story: [
        'bg-gradient-to-br from-amber-400/90 to-rose-900',
        'bg-gradient-to-br from-violet-500/90 to-slate-900',
        'bg-gradient-to-br from-emerald-400/90 to-slate-900',
    ],
};

/** Tinggi punggung tiruan untuk pratinjau rak kayu. */
const spineHeights = ['2.1rem', '1.6rem', '2.3rem', '1.75rem'];

/** Sudut kemiringan polaroid tiruan untuk pratinjau rak kafe. */
const cardTilts = ['1.4deg', '-1.6deg'];

/**
 * Kelas warna sampul ke-`index`, berulang kalau jumlahnya kurang.
 */
function cover(index: number): string {
    const palette = covers[props.theme] ?? covers.wood;

    return palette[index % palette.length];
}
</script>

<template>
    <div
        :data-shelf-theme="props.theme"
        :class="
            cn(
                'overflow-hidden rounded-2xl border border-black/5 p-3.5 shadow-inner',
                props.theme === 'neon' && 'dark',
                props.class,
            )
        "
        :style="{
            backgroundColor: 'var(--shelf-surface)',
            borderRadius: 'var(--shelf-radius)',
        }"
        aria-hidden="true"
    >
        <div
            class="mb-2 text-[9px] font-semibold tracking-[0.2em] text-shelf-muted uppercase"
        >
            Sudah dibaca
        </div>

        <!-- Rak kayu: buku berdiri di atas papan. -->
        <div
            v-if="props.theme === 'wood'"
            class="flex min-h-20 flex-col justify-end"
        >
            <div class="flex items-end justify-center gap-1">
                <span
                    v-for="(height, index) in spineHeights"
                    :key="index"
                    :class="cn('w-3 rounded-t-[2px] shadow-sm', cover(index))"
                    :style="{ height }"
                />
            </div>
            <div class="shelf-plank mt-0.5 h-2 rounded-sm" />
        </div>

        <!-- Rak minimalis: daftar indeks bergaris tipis. -->
        <div v-else-if="props.theme === 'minimal'" class="min-h-20 space-y-1.5">
            <div
                v-for="index in 3"
                :key="index"
                class="flex items-center gap-2 border-b border-shelf-plank pb-1.5"
            >
                <span class="font-mono text-[7px] text-shelf-muted">
                    0{{ index }}
                </span>
                <span :class="cn('h-6 w-4 rounded-[2px]', cover(index - 1))" />
                <span class="flex-1 space-y-1">
                    <span
                        class="block h-1.5 w-3/4 rounded-full bg-shelf-ink/25"
                    />
                    <span
                        class="block h-1 w-1/2 rounded-full bg-shelf-muted/40"
                    />
                </span>
            </div>
        </div>

        <!-- Rak neon: kisi kotak dengan garis menyala. -->
        <div
            v-else-if="props.theme === 'neon'"
            class="flex min-h-20 flex-col justify-end"
        >
            <div class="grid grid-cols-3 gap-1.5">
                <span
                    v-for="index in 3"
                    :key="index"
                    :class="
                        cn(
                            'aspect-2/3 rounded-[3px] border border-shelf-accent/40',
                            cover(index - 1),
                        )
                    "
                />
            </div>
            <div class="relative mt-2">
                <div class="shelf-plank h-1 rounded-full" />
                <div
                    class="absolute inset-x-0 top-1/2 h-3 -translate-y-1/2 bg-shelf-glow/30 blur-md"
                />
            </div>
        </div>

        <!-- Rak kafe: polaroid miring di papan. -->
        <div
            v-else-if="props.theme === 'cozy'"
            class="flex min-h-20 items-end justify-center gap-3"
        >
            <span
                v-for="index in 2"
                :key="index"
                class="block w-12 rounded-[4px] bg-shelf-surface-2 p-1 shadow-sm"
                :style="{ transform: `rotate(${cardTilts[index - 1]})` }"
            >
                <span
                    :class="
                        cn(
                            'block aspect-2/3 w-full rounded-[2px]',
                            cover(index - 1),
                        )
                    "
                />
                <span
                    class="mt-1 block h-1 w-3/4 rounded-full bg-shelf-muted/40"
                />
            </span>
        </div>

        <!-- Rak reel cerita: kartu vertikal full-cover yang mengintip. -->
        <div v-else class="flex min-h-20 items-end justify-center gap-1.5">
            <span
                v-for="index in 3"
                :key="index"
                :class="
                    cn(
                        'relative block w-11 overflow-hidden rounded-[8px] border border-white/10',
                        cover(index - 1),
                        index === 2 ? 'aspect-[9/14] w-14' : 'aspect-[9/14] opacity-70',
                    )
                "
            >
                <span
                    class="absolute inset-x-1.5 top-1.5 block h-0.5 overflow-hidden rounded-full bg-white/30"
                >
                    <span
                        class="block h-full rounded-full bg-white/90"
                        :style="{ width: `${(index / 3) * 100}%` }"
                    />
                </span>
                <span
                    class="absolute inset-x-1.5 bottom-1.5 block space-y-1"
                >
                    <span class="block h-1 w-full rounded-full bg-white/85" />
                    <span class="block h-1 w-2/3 rounded-full bg-white/50" />
                </span>
            </span>
        </div>
    </div>
</template>
