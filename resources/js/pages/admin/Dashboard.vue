<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Ban,
    BookOpen,
    CheckCheck,
    Eye,
    EyeOff,
    LayoutDashboard,
    Link2,
    ScanSearch,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import StatCard from '@/components/bookshelf/StatCard.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useInitials } from '@/composables/useInitials';
import { formatDate, formatNumber } from '@/lib/format';
import { dashboard } from '@/routes';
import { index as profanityIndex } from '@/routes/admin/profanity';
import { index as usersIndex } from '@/routes/admin/users';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard Admin', href: '/admin' }],
    },
});

type AdminStats = {
    users: number;
    admins: number;
    blocked: number;
    books: number;
    finished: number;
    publicShelves: number;
    unlistedShelves: number;
    privateShelves: number;
};

type RecentUser = {
    id: number;
    name: string;
    username: string | null;
    email: string;
    role: string;
    role_label: string;
    is_blocked: boolean;
    joined_at: string | null;
    shelf_url: string | null;
};

const props = defineProps<{
    stats: AdminStats;
    recentUsers: RecentUser[];
}>();

const { getInitials } = useInitials();
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between sm:p-6"
        >
            <div class="space-y-1.5">
                <p
                    class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-[0.18em] text-primary uppercase"
                >
                    <LayoutDashboard class="size-3.5" />
                    Admin
                </p>
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    Dashboard Admin
                </h1>
                <p class="text-sm text-muted-foreground">
                    Ringkasan operasional aplikasi. Untuk rak pribadimu, buka
                    dashboard pengguna.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button as-child variant="outline">
                    <Link :href="dashboard()">
                        <Eye class="size-4" />
                        Dashboard saya
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="usersIndex()">
                        <Users class="size-4" />
                        Kelola pengguna
                    </Link>
                </Button>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                label="Total pengguna"
                :value="formatNumber(props.stats.users)"
                :icon="Users"
                tone="primary"
            />
            <StatCard
                label="Admin"
                :value="formatNumber(props.stats.admins)"
                :icon="ShieldCheck"
            />
            <StatCard
                label="Diblokir"
                :value="formatNumber(props.stats.blocked)"
                :icon="Ban"
            />
            <StatCard
                label="Total buku"
                :value="formatNumber(props.stats.books)"
                :icon="BookOpen"
            />
            <StatCard
                label="Buku tamat"
                :value="formatNumber(props.stats.finished)"
                :icon="CheckCheck"
            />
            <StatCard
                label="Rak publik"
                :value="formatNumber(props.stats.publicShelves)"
                :icon="Eye"
            />
            <StatCard
                label="Rak unlisted"
                :value="formatNumber(props.stats.unlistedShelves)"
                :icon="Link2"
            />
            <StatCard
                label="Rak privat"
                :value="formatNumber(props.stats.privateShelves)"
                :icon="EyeOff"
            />
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div
                class="rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
            >
                <h2 class="font-display text-lg text-foreground">
                    Moderasi cepat
                </h2>
                <p
                    class="mt-1 text-sm leading-relaxed text-muted-foreground"
                >
                    Tindak lanjuti akun bermasalah atau pindai kata kasar di
                    seluruh data.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <Button as-child size="sm">
                        <Link :href="usersIndex()">
                            <Users class="size-4" />
                            Kelola pengguna
                        </Link>
                    </Button>
                    <Button as-child size="sm" variant="outline">
                        <Link :href="profanityIndex()">
                            <ScanSearch class="size-4" />
                            Scan kata kasar
                        </Link>
                    </Button>
                </div>
            </div>

            <div
                class="rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
            >
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-display text-lg text-foreground">
                        Pengguna terbaru
                    </h2>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="usersIndex()">Lihat semua</Link>
                    </Button>
                </div>

                <ul class="mt-4 space-y-3">
                    <li
                        v-for="user in props.recentUsers"
                        :key="user.id"
                        class="flex items-center gap-3"
                    >
                        <Avatar
                            class="h-9 w-9 overflow-hidden rounded-lg"
                        >
                            <AvatarFallback class="rounded-lg text-xs">
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-medium text-foreground"
                            >
                                {{ user.name }}
                                <span
                                    v-if="user.username"
                                    class="font-normal text-muted-foreground"
                                >
                                    · @{{ user.username }}
                                </span>
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ user.role_label }}
                                <template v-if="user.joined_at">
                                    · {{ formatDate(user.joined_at) }}
                                </template>
                            </p>
                        </div>
                        <Badge
                            :variant="
                                user.is_blocked ? 'destructive' : 'secondary'
                            "
                        >
                            {{ user.is_blocked ? 'Diblokir' : 'Aktif' }}
                        </Badge>
                    </li>
                </ul>

                <p
                    v-if="props.recentUsers.length === 0"
                    class="mt-4 text-sm text-muted-foreground"
                >
                    Belum ada pengguna.
                </p>
            </div>
        </section>
    </div>
</template>
