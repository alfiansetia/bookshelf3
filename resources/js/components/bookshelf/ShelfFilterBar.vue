<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
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
    ShelfFilters,
    SortOption,
    StatusOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        filters: ShelfFilters;
        statuses: StatusOption[];
        languages: { value: string; label: string; total?: number }[];
        sortOptions: SortOption[];
        url: string;
        statusCounts?: Record<string, number>;
        showFavorite?: boolean;
        total?: number;
        class?: string;
    }>(),
    {
        statusCounts: () => ({}),
        showFavorite: true,
        total: undefined,
    },
);

const search = ref(props.filters.search ?? '');
const favorite = ref(props.filters.favorite);

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    },
);

watch(
    () => props.filters.favorite,
    (value) => {
        favorite.value = value;
    },
);

const allCount = computed(() =>
    Object.values(props.statusCounts).reduce(
        (total, count) => total + count,
        0,
    ),
);

const hasActiveFilters = computed(
    () =>
        Boolean(props.filters.search) ||
        Boolean(props.filters.status) ||
        Boolean(props.filters.language) ||
        props.filters.favorite ||
        (props.filters.sort !== 'recent' && props.sortOptions.length > 0),
);

function params(overrides: Partial<ShelfFilters> = {}): Record<string, string> {
    const merged = { ...props.filters, ...overrides };
    const query: Record<string, string> = {
        sort: merged.sort,
    };

    if (merged.search) {
        query.search = merged.search;
    }

    if (merged.status) {
        query.status = merged.status;
    }

    if (merged.language) {
        query.language = merged.language;
    }

    if (merged.favorite) {
        query.favorite = '1';
    }

    return query;
}

function visit(overrides: Partial<ShelfFilters> = {}, replace = false): void {
    router.get(props.url, params(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace,
    });
}

const visitWithDebounce = useDebounceFn(() => {
    visit({ search: search.value.trim() || null }, true);
}, 350);

function toggleStatus(value: BookStatus | null): void {
    visit({ status: props.filters.status === value ? null : value });
}

function changeLanguage(value: string): void {
    visit({ language: value === 'all' ? null : value });
}

function changeSort(value: string): void {
    visit({ sort: value });
}

function toggleFavorite(): void {
    visit({ favorite: !props.filters.favorite });
}

function reset(): void {
    search.value = '';
    favorite.value = false;
    visit({
        search: null,
        status: null,
        language: null,
        favorite: false,
        sort: 'recent',
    });
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
                    v-model="search"
                    type="search"
                    :placeholder="'Cari judul, penulis, atau penerbit...'"
                    class="pl-9"
                    aria-label="Cari buku"
                    @input="visitWithDebounce"
                />
            </div>

            <div class="flex items-center gap-2">
                <Select
                    :model-value="props.filters.language ?? 'all'"
                    @update:model-value="changeLanguage($event as string)"
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
                    :model-value="props.filters.sort"
                    @update:model-value="changeSort($event as string)"
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
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        props.filters.status === null
                            ? 'border-primary/40 bg-primary/10 text-primary'
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="toggleStatus(null)"
            >
                <SlidersHorizontal class="size-3.5" />
                Semua
                <span class="opacity-70">({{ formatNumber(allCount) }})</span>
            </button>

            <button
                v-for="status in props.statuses"
                :key="status.value"
                type="button"
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        props.filters.status === status.value
                            ? cn(
                                  statusStyle(status.color).badge,
                                  'border-transparent ring-1',
                              )
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="toggleStatus(status.value)"
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
                :class="
                    cn(
                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                        favorite
                            ? 'border-rose-300 bg-rose-100 text-rose-900 dark:border-rose-500/40 dark:bg-rose-500/15 dark:text-rose-200'
                            : 'border-border/70 bg-card text-muted-foreground hover:text-foreground',
                    )
                "
                @click="toggleFavorite"
            >
                <Heart :class="cn('size-3.5', favorite && 'fill-current')" />
                Favorit
            </button>

            <Button
                v-if="hasActiveFilters"
                type="button"
                variant="ghost"
                size="sm"
                class="ml-auto text-muted-foreground"
                @click="reset"
            >
                <RotateCcw class="size-3.5" />
                Reset filter
            </Button>
        </div>

        <p
            v-if="props.total !== undefined"
            class="text-xs text-muted-foreground"
        >
            Menampilkan {{ formatNumber(props.total) }} buku
        </p>
    </div>
</template>
