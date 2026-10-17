<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { cn } from '@/lib/utils';
import type { Paginated, PaginationLink } from '@/types';

const props = defineProps<{
    paginator: Paginated<unknown>;
    class?: string;
}>();

const previousUrl = computed(() => props.paginator.links[0]?.url ?? null);
const nextUrl = computed(
    () => props.paginator.links[props.paginator.links.length - 1]?.url ?? null,
);

const pageLinks = computed<PaginationLink[]>(() =>
    props.paginator.links.slice(1, -1),
);
</script>

<template>
    <nav
        v-if="props.paginator.last_page > 1"
        :class="
            cn(
                'flex flex-wrap items-center justify-center gap-1.5',
                props.class,
            )
        "
        aria-label="Navigasi halaman"
    >
        <Link
            v-if="previousUrl"
            :href="previousUrl"
            preserve-scroll
            preserve-state
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border/70 bg-card text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
            aria-label="Halaman sebelumnya"
        >
            <ChevronLeft class="size-4" />
        </Link>
        <span
            v-else
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border/50 bg-muted/40 text-muted-foreground/50"
            aria-hidden="true"
        >
            <ChevronLeft class="size-4" />
        </span>

        <template
            v-for="(link, index) in pageLinks"
            :key="`${link.label}-${index}`"
        >
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                preserve-state
                :class="
                    cn(
                        'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2.5 text-sm transition-colors',
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border/70 bg-card text-muted-foreground hover:border-primary/40 hover:text-primary',
                    )
                "
                :aria-current="link.active ? 'page' : undefined"
            >
                {{
                    link.label
                        .replace('&laquo;', '')
                        .replace('&raquo;', '')
                        .trim() || '…'
                }}
            </Link>
            <span
                v-else
                class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-sm text-muted-foreground"
            >
                {{ link.label }}
            </span>
        </template>

        <Link
            v-if="nextUrl"
            :href="nextUrl"
            preserve-scroll
            preserve-state
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border/70 bg-card text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
            aria-label="Halaman berikutnya"
        >
            <ChevronRight class="size-4" />
        </Link>
        <span
            v-else
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border/50 bg-muted/40 text-muted-foreground/50"
            aria-hidden="true"
        >
            <ChevronRight class="size-4" />
        </span>
    </nav>
</template>
