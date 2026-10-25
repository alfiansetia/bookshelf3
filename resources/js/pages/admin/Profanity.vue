<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    BookOpen,
    ExternalLink,
    RefreshCw,
    ScanSearch,
    Users,
} from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import StatCard from '@/components/bookshelf/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as profanityIndex } from '@/routes/admin/profanity';
import { index as usersIndex } from '@/routes/admin/users';
import { formatDate, formatNumber } from '@/lib/format';
import type {
    ProfanityBookFinding,
    ProfanityScanSummary,
    ProfanityUserFinding,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola pengguna', href: usersIndex() },
            { title: 'Scan kata kasar', href: profanityIndex() },
        ],
    },
});

const props = defineProps<{
    summary: ProfanityScanSummary;
    userFindings: ProfanityUserFinding[];
    bookFindings: ProfanityBookFinding[];
    scannedAt: string;
}>();

const totalHits = computed(
    () => props.summary.user_hits + props.summary.book_hits,
);
const isClean = computed(() => totalHits.value === 0);

function rescan(): void {
    router.reload({
        only: ['summary', 'userFindings', 'bookFindings', 'scannedAt'],
    });
}
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1.5">
                    <h1
                        class="font-display text-2xl text-foreground sm:text-3xl"
                    >
                        Scan kata kasar
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Pindai username, profil, tautan sosial, dan buku yang
                        sudah tersimpan. Dipindai
                        {{ formatNumber(props.summary.words_count) }} kata pada
                        {{ formatDate(props.scannedAt) }}.
                    </p>
                </div>
                <Button variant="outline" size="sm" @click="rescan">
                    <RefreshCw class="size-4" />
                    Pindai ulang
                </Button>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <StatCard
                    label="Pengguna dipindai"
                    :value="formatNumber(props.summary.users_scanned)"
                    :icon="Users"
                />
                <StatCard
                    label="Buku dipindai"
                    :value="formatNumber(props.summary.books_scanned)"
                    :icon="BookOpen"
                />
                <StatCard
                    label="Temuan pengguna"
                    :value="formatNumber(props.summary.user_hits)"
                    :icon="ScanSearch"
                    tone="primary"
                />
                <StatCard
                    label="Temuan buku"
                    :value="formatNumber(props.summary.book_hits)"
                    :icon="ScanSearch"
                />
            </div>
        </section>

        <EmptyState
            v-if="isClean"
            :icon="ScanSearch"
            title="Bersih, tidak ada kata kasar"
            description="Semua username, profil, dan buku yang tersimpan lolos dari daftar kata di config."
        />

        <template v-else>
            <section
                v-if="props.userFindings.length > 0"
                class="overflow-hidden rounded-2xl border border-border/70 bg-card shadow-xs"
            >
                <div class="border-b border-border/70 bg-muted/40 px-4 py-3">
                    <h2 class="font-medium text-foreground">
                        Temuan pengguna ({{
                            formatNumber(props.userFindings.length)
                        }})
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-4xl text-left text-sm">
                        <thead
                            class="border-b border-border/70 text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Pengguna</th>
                                <th class="px-4 py-3 font-medium">Kolom</th>
                                <th class="px-4 py-3 font-medium">Kutipan</th>
                                <th class="px-4 py-3 font-medium">
                                    Kata cocok
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/70">
                            <tr
                                v-for="finding in props.userFindings"
                                :key="`${finding.id}-${finding.field}`"
                                class="align-top"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">
                                        {{ finding.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            finding.username
                                                ? `@${finding.username}`
                                                : 'Tanpa username'
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ finding.email }}
                                    </p>
                                    <Badge
                                        v-if="finding.is_blocked"
                                        variant="destructive"
                                        class="mt-1"
                                    >
                                        Diblokir
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ finding.field_label }}
                                </td>
                                <td
                                    class="max-w-md px-4 py-3 text-muted-foreground"
                                >
                                    {{ finding.snippet }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="word in finding.matched"
                                            :key="word"
                                            variant="destructive"
                                        >
                                            {{ word }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end">
                                        <Button
                                            v-if="finding.shelf_url"
                                            as-child
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <a
                                                :href="finding.shelf_url"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                <ExternalLink class="size-4" />
                                                Rak
                                            </a>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                v-if="props.bookFindings.length > 0"
                class="overflow-hidden rounded-2xl border border-border/70 bg-card shadow-xs"
            >
                <div class="border-b border-border/70 bg-muted/40 px-4 py-3">
                    <h2 class="font-medium text-foreground">
                        Temuan buku ({{
                            formatNumber(props.bookFindings.length)
                        }})
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-4xl text-left text-sm">
                        <thead
                            class="border-b border-border/70 text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Buku</th>
                                <th class="px-4 py-3 font-medium">Kolom</th>
                                <th class="px-4 py-3 font-medium">Kutipan</th>
                                <th class="px-4 py-3 font-medium">
                                    Kata cocok
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/70">
                            <tr
                                v-for="finding in props.bookFindings"
                                :key="`${finding.id}-${finding.field}`"
                                class="align-top"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">
                                        {{ finding.title }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            finding.owner_username
                                                ? `milik @${finding.owner_username}`
                                                : (finding.owner_name ??
                                                  'Pemilik tidak diketahui')
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ finding.field_label }}
                                </td>
                                <td
                                    class="max-w-md px-4 py-3 text-muted-foreground"
                                >
                                    {{ finding.snippet }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="word in finding.matched"
                                            :key="word"
                                            variant="destructive"
                                        >
                                            {{ word }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end">
                                        <Button
                                            v-if="finding.shelf_url"
                                            as-child
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <a
                                                :href="finding.shelf_url"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                <ExternalLink class="size-4" />
                                                Buku
                                            </a>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </template>
    </div>
</template>
