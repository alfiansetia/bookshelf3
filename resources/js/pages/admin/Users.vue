<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Ban,
    ExternalLink,
    Search,
    ShieldCheck,
    UserCheck,
    Users,
} from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ShelfPagination from '@/components/bookshelf/ShelfPagination.vue';
import StatCard from '@/components/bookshelf/StatCard.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useInitials } from '@/composables/useInitials';
import { formatDate, formatNumber } from '@/lib/format';
import {
    block,
    index as usersIndex,
    role as updateRole,
    unblock,
} from '@/routes/admin/users';
import type {
    AdminUserFilters,
    AdminUserRow,
    AdminUserStats,
    Paginated,
    RoleOption,
    UserRole,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Kelola pengguna', href: usersIndex() }],
    },
});

const props = defineProps<{
    users: Paginated<AdminUserRow>;
    filters: AdminUserFilters;
    stats: AdminUserStats;
    roles: RoleOption[];
    reasonMaxLength: number;
    currentUserId: number | null;
}>();

const { getInitials } = useInitials();

const hasUsers = computed(() => props.users.data.length > 0);

const search = ref(props.filters.search ?? '');
const busyId = ref<number | null>(null);

const blockDialogOpen = ref(false);
const blockTarget = ref<AdminUserRow | null>(null);
const blockForm = useForm({ reason: '' });

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    },
);

/**
 * Kirim ulang filter ke server tanpa kehilangan posisi scroll.
 */
function visit(overrides: Partial<AdminUserFilters> = {}): void {
    const merged = { ...props.filters, ...overrides };
    const query: Record<string, string> = {};

    if (merged.search) {
        query.search = merged.search;
    }

    if (merged.status && merged.status !== 'all') {
        query.status = merged.status;
    }

    if (merged.role && merged.role !== 'all') {
        query.role = merged.role;
    }

    router.get(usersIndex.url(), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const searchDebounced = useDebounceFn(
    () => visit({ search: search.value.trim() || null }),
    350,
);

function isCurrentUser(user: AdminUserRow): boolean {
    return props.currentUserId !== null && user.id === props.currentUserId;
}

function openBlockDialog(user: AdminUserRow): void {
    blockTarget.value = user;
    blockForm.reset();
    blockForm.clearErrors();
    blockDialogOpen.value = true;
}

function submitBlock(): void {
    const target = blockTarget.value;

    if (!target) {
        return;
    }

    busyId.value = target.id;

    blockForm.patch(block.url(target.id), {
        preserveScroll: true,
        onSuccess: () => {
            blockDialogOpen.value = false;
            blockForm.reset();
        },
        onFinish: () => {
            busyId.value = null;
        },
    });
}

function unblockUser(user: AdminUserRow): void {
    busyId.value = user.id;

    router.delete(unblock.url(user.id), {
        preserveScroll: true,
        onFinish: () => {
            busyId.value = null;
        },
    });
}

function changeRole(user: AdminUserRow, value: UserRole): void {
    if (value === user.role) {
        return;
    }

    busyId.value = user.id;

    router.patch(
        updateRole.url(user.id),
        { role: value },
        {
            preserveScroll: true,
            onFinish: () => {
                busyId.value = null;
            },
        },
    );
}
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <section
            class="flex flex-col gap-4 rounded-2xl border border-border/70 bg-card p-5 shadow-xs"
        >
            <div class="space-y-1.5">
                <h1 class="font-display text-2xl text-foreground sm:text-3xl">
                    Kelola pengguna
                </h1>
                <p class="text-sm text-muted-foreground">
                    Blokir akun yang melanggar aturan beserta alasannya, dan
                    atur siapa saja yang jadi admin.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <StatCard
                    label="Total pengguna"
                    :value="formatNumber(props.stats.total)"
                    :icon="Users"
                />
                <StatCard
                    label="Admin"
                    :value="formatNumber(props.stats.admins)"
                    :icon="ShieldCheck"
                    tone="primary"
                />
                <StatCard
                    label="Diblokir"
                    :value="formatNumber(props.stats.blocked)"
                    :icon="Ban"
                />
            </div>
        </section>

        <section
            class="flex flex-col gap-3 rounded-2xl border border-border/70 bg-card p-4 shadow-xs sm:flex-row sm:items-end"
        >
            <div class="flex-1 space-y-2">
                <Label for="search">Cari pengguna</Label>
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="search"
                        v-model="search"
                        class="pl-9"
                        placeholder="Nama, username, atau email"
                        @input="searchDebounced"
                    />
                </div>
            </div>

            <div class="space-y-2 sm:w-44">
                <Label for="status">Status</Label>
                <Select
                    :model-value="props.filters.status"
                    @update:model-value="
                        (value) =>
                            visit({
                                status: value as AdminUserFilters['status'],
                            })
                    "
                >
                    <SelectTrigger id="status" class="w-full">
                        <SelectValue placeholder="Semua" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua</SelectItem>
                        <SelectItem value="active">Aktif</SelectItem>
                        <SelectItem value="blocked">Diblokir</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2 sm:w-44">
                <Label for="role">Peran</Label>
                <Select
                    :model-value="props.filters.role"
                    @update:model-value="
                        (value) =>
                            visit({ role: value as AdminUserFilters['role'] })
                    "
                >
                    <SelectTrigger id="role" class="w-full">
                        <SelectValue placeholder="Semua" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua</SelectItem>
                        <SelectItem
                            v-for="option in props.roles"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </section>

        <EmptyState
            v-if="!hasUsers"
            :icon="Users"
            title="Tidak ada pengguna yang cocok"
            description="Coba ubah kata kunci pencarian atau filternya."
        />

        <section
            v-else
            class="overflow-hidden rounded-2xl border border-border/70 bg-card shadow-xs"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-4xl text-left text-sm">
                    <thead
                        class="border-b border-border/70 bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Pengguna</th>
                            <th class="px-4 py-3 font-medium">Peran</th>
                            <th class="px-4 py-3 font-medium">Buku</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr
                            v-for="user in props.users.data"
                            :key="user.id"
                            class="align-top"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <Avatar
                                        class="h-9 w-9 overflow-hidden rounded-lg"
                                    >
                                        <AvatarImage
                                            v-if="user.avatar_url"
                                            :src="user.avatar_url"
                                            :alt="user.name"
                                        />
                                        <AvatarFallback
                                            class="rounded-lg text-black dark:text-white"
                                        >
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div class="space-y-0.5">
                                        <p class="font-medium text-foreground">
                                            {{ user.name }}
                                            <Badge
                                                v-if="isCurrentUser(user)"
                                                variant="outline"
                                                class="ml-1 align-middle"
                                            >
                                                Kamu
                                            </Badge>
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                user.username
                                                    ? `@${user.username}`
                                                    : 'Tanpa username'
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <Select
                                    v-if="!isCurrentUser(user)"
                                    :model-value="user.role"
                                    :disabled="busyId === user.id"
                                    @update:model-value="
                                        (value) =>
                                            changeRole(user, value as UserRole)
                                    "
                                >
                                    <SelectTrigger class="w-40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in props.roles"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <Badge v-else variant="secondary">
                                    {{ user.role_label }}
                                </Badge>
                            </td>

                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatNumber(user.books_count) }}
                            </td>

                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        user.is_blocked
                                            ? 'destructive'
                                            : 'secondary'
                                    "
                                >
                                    {{ user.is_blocked ? 'Diblokir' : 'Aktif' }}
                                </Badge>

                                <div
                                    v-if="user.is_blocked"
                                    class="mt-2 max-w-72 space-y-1"
                                >
                                    <p
                                        class="text-xs leading-relaxed text-muted-foreground"
                                    >
                                        {{ user.blocked_reason }}
                                    </p>
                                    <p class="text-xs text-muted-foreground/80">
                                        {{ user.blocked_by ?? 'Admin' }}
                                        <template v-if="user.blocked_at">
                                            · {{ formatDate(user.blocked_at) }}
                                        </template>
                                    </p>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div
                                    class="flex flex-wrap items-center justify-end gap-2"
                                >
                                    <Button
                                        v-if="user.shelf_url"
                                        as-child
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <a
                                            :href="user.shelf_url"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <ExternalLink class="size-4" />
                                            Rak
                                        </a>
                                    </Button>

                                    <Button
                                        v-if="user.is_blocked"
                                        variant="outline"
                                        size="sm"
                                        :disabled="busyId === user.id"
                                        @click="unblockUser(user)"
                                    >
                                        <UserCheck class="size-4" />
                                        Buka blokir
                                    </Button>

                                    <Button
                                        v-else
                                        variant="destructive"
                                        size="sm"
                                        :disabled="
                                            isCurrentUser(user) ||
                                            user.role === 'admin'
                                        "
                                        :title="
                                            isCurrentUser(user)
                                                ? 'Kamu tidak bisa memblokir akunmu sendiri.'
                                                : user.role === 'admin'
                                                  ? 'Admin tidak bisa diblokir. Turunkan dulu perannya.'
                                                  : undefined
                                        "
                                        @click="openBlockDialog(user)"
                                    >
                                        <Ban class="size-4" />
                                        Blokir
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <ShelfPagination :paginator="props.users" />

        <Dialog v-model:open="blockDialogOpen">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>
                        Blokir {{ blockTarget?.name ?? 'pengguna' }}?
                    </DialogTitle>
                    <DialogDescription>
                        Akun ini tidak bisa masuk lagi dan rak publiknya berubah
                        jadi halaman keterangan blokir. Alasanmu akan dibaca
                        siapa pun yang membuka raknya.
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-2" @submit.prevent="submitBlock">
                    <Label for="reason">Alasan blokir</Label>
                    <Textarea
                        id="reason"
                        v-model="blockForm.reason"
                        :maxlength="props.reasonMaxLength"
                        rows="4"
                        placeholder="Contoh: mengunggah konten yang tidak pantas."
                    />
                    <InputError :message="blockForm.errors.reason" />
                </form>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button">
                            Batal
                        </Button>
                    </DialogClose>

                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="blockForm.processing"
                        @click="submitBlock"
                    >
                        <Ban class="size-4" />
                        Blokir akun
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
