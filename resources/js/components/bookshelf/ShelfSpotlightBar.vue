<script setup lang="ts">
import { computed } from 'vue';
import { Heart, RotateCcw, Search, SlidersHorizontal } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { iconByName } from '@/lib/icons';
import { statusStyle } from '@/lib/statusStyles';
import { cn } from '@/lib/utils';
import { formatNumber } from '@/lib/format';
import type {
    BookStatus,
    SortOption,
    StatusOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        search: string;
        spotlight: BookStatus | null;
        language: string | null;
        sort: string;
        favorite: boolean;
        statuses: StatusOption[];
        languages: { value: string; label: string; total?: number }[];
        sortOptions: SortOption[];
        statusCounts?: Record<string, number>;
        showFavorite?: boolean;
        shown?: number;
        total?: number;
        class?: string;
    }>(),
    {
        statusCounts: () => ({}),
        showFavorite: true,
        shown: undefined,
        total: undefined,
    },
);

const emit = defineEmits<{
    (e: 'update:search', value: string): void;
    (e: 'update:spotlight', value: BookStatus | null): void;
    (e: 'update:language', value: string | null): void;
    (e: 'update:sort', value: string): void;
    (e: 'update:favorite', value: boolean): void;
    (e: 'reset'): void;
}>();

const allCount = computed(() =>
    Object.values(props.statusCounts).reduce(
        (total, count) => total + count,
        0,
    ),
);

const hasActiveFilters = computed(
    () =>
        props.search.trim() !== '' ||
        props.spotlight !== null ||
        props.language !== null ||
        props.favorite ||
        props.sort !== 'recent',
);

function toggleSpotlight(value: BookStatus | null): void {
    emit('update:spotlight', props.spotlight === value ? null : value);
}
</script>

<template>
    <div
        :class="
            cn(
                'space-y-4 rounded-2xl border border-border/70 bg-card/70 p-4 shadow-xs backdrop-blur-sm',
                props.class,
            )
        "
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    :model-value="props.search"
                    type="search"
                    placeholder="Cari judul atau penulis..."
                    class="pl-9"
                    aria-label="Cari buku"
                    @update:model-value="emit('update:search', $event as string)"
                />
            </div>

            <div class="flex items-center gap-2">
                <Select
                    :model-value="props.language ?? 'all'"
                    @update:model-value="
                        emit(
                            'update:language',
                            $event === 'all' ? null : ($event as string),
                        )
                    "
                >
                    <SelectTrigger
                        class="w-[9.5rem]"
                        aria-label="Filter bahasa"
                    >
                        <SelectValue placeholder="Semua bahasa" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua bahasa</SelectItem>
                        <SelectItem
                            v-for="language in props.languages"
                            :key="language.value"
                            :value="language.value"
                        >
                            {{ language.label
                            }}<span
                                v-if="language.total"
                                class="text-muted-foreground"
                            >
                                ({{ language.total }})</span
                            >
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    v-if="props.sortOptions.length > 0"
                    :model-value="props.sort"
                    @update:model-value="emit('update:sort', $event as string)"
                >
                    <SelectTrigger class="w-[10.5rem]" aria-label="Urutkan">
                        <SelectValue placeholder="Urutkan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.sortOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                :aria-pressed="props.spotlight === null"
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        props.spotlight === null
                            ? 'border-primary/40 bg-primary/10 text-primary'
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="toggleSpotlight(null)"
            >
                <SlidersHorizontal class="size-3.5" />
                Semua
                <span class="opacity-70">({{ formatNumber(allCount) }})</span>
            </button>

            <button
                v-for="status in props.statuses"
                :key="status.value"
                type="button"
                :aria-pressed="props.spotlight === status.value"
                :title="`Sorot ${status.label}, status lain diredupkan`"
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        props.spotlight === status.value
                            ? cn(
                                  statusStyle(status.color).badge,
                                  'border-transparent ring-1',
                              )
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="toggleSpotlight(status.value)"
            >
                <component :is="iconByName(status.icon)" class="size-3.5" />
                {{ status.label }}
                <span class="opacity-70">
                    ({{ formatNumber(props.statusCounts[status.value] ?? 0) }})
                </span>
            </button>

            <button
                v-if="props.showFavorite"
                type="button"
                :aria-pressed="props.favorite"
                title="Sorot favorit, yang lain diredupkan"
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        props.favorite
                            ? 'border-rose-300 bg-rose-100 text-rose-900 dark:border-rose-500/40 dark:bg-rose-500/15 dark:text-rose-200'
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="emit('update:favorite', !props.favorite)"
            >
                <Heart
                    :class="cn('size-3.5', props.favorite && 'fill-current')"
                />
                Favorit
            </button>

            <Button
                v-if="hasActiveFilters"
                type="button"
                variant="ghost"
                size="sm"
                class="ml-auto text-muted-foreground"
                @click="emit('reset')"
            >
                <RotateCcw class="size-3.5" />
                Reset filter
            </Button>
        </div>

        <p
            v-if="props.total !== undefined"
            class="text-xs text-muted-foreground"
        >
            <template v-if="props.shown !== undefined && props.shown < props.total">
                Menampilkan {{ formatNumber(props.shown) }} dari
                {{ formatNumber(props.total) }} buku
            </template>
            <template v-else>
                Menampilkan {{ formatNumber(props.total) }} buku
            </template>
        </p>
    </div>
</template>
