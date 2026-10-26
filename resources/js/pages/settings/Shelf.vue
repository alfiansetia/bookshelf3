<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, Loader2, Palette, Save } from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ShelfRenderer from '@/components/bookshelf/shelves/ShelfRenderer.vue';
import ShelfThemePicker from '@/components/bookshelf/ShelfThemePicker.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';
import { edit as editShelf, update } from '@/routes/shelf';
import type {
    ShelfBook,
    ShelfSummary,
    ShelfTheme,
    ShelfThemeOption,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Gaya rak', href: editShelf() }],
    },
});

const props = defineProps<{
    themes: ShelfThemeOption[];
    selectedTheme: ShelfTheme;
    isPublic: boolean;
    shelfUrl: string | null;
    username: string | null;
    stats: ShelfSummary;
    previewBooks: ShelfBook[];
}>();

const form = useForm({
    theme: props.selectedTheme,
    is_public: props.isPublic,
});

const activeTheme = computed(
    () =>
        props.themes.find((theme) => theme.value === form.theme) ??
        props.themes[0],
);

function chooseTheme(theme: ShelfTheme): void {
    form.theme = theme;
}

function save(): void {
    form.patch(update.url(), { preserveScroll: true });
}
</script>

<template>
    <Head title="Gaya rak" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Gaya rak"
            description="Pilih tampilan rak publikmu dan tentukan siapa yang boleh melihatnya."
        />

        <form class="space-y-6" @submit.prevent="save">
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <Palette class="size-4 text-primary" />
                    <p class="text-sm font-medium text-foreground">
                        Pilih gaya rak
                    </p>
                </div>

                <ShelfThemePicker
                    :themes="props.themes"
                    :model-value="form.theme"
                    @update:model-value="chooseTheme"
                />
                <InputError :message="form.errors.theme" />
            </div>

            <div class="space-y-3">
                <p class="text-sm font-medium text-foreground">
                    Pratinjau langsung
                </p>

                <ShelfRenderer
                    :theme="form.theme"
                    :books="previewBooks"
                    owner
                    class="min-h-40"
                >
                    <template #empty>
                        <EmptyState
                            title="Belum ada buku untuk dipratinjau"
                            description="Tambahkan buku terlebih dahulu untuk melihat bagaimana rakmu akan tampil."
                        />
                    </template>
                </ShelfRenderer>

                <p v-if="activeTheme" class="text-xs text-muted-foreground">
                    {{ activeTheme.label }} — {{ activeTheme.description }}
                </p>
            </div>

            <div
                class="flex items-start justify-between gap-4 rounded-2xl border border-border/70 bg-card p-4"
            >
                <div class="flex items-start gap-3">
                    <span
                        :class="
                            cn(
                                'flex size-9 items-center justify-center rounded-xl',
                                form.is_public
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                    : 'bg-muted text-muted-foreground',
                            )
                        "
                    >
                        <Eye v-if="form.is_public" class="size-4" />
                        <EyeOff v-else class="size-4" />
                    </span>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-foreground">
                            Tampilkan rak ke publik
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Kalau dimatikan, hanya kamu yang bisa membuka
                            halaman rak ini.
                        </p>
                        <p
                            v-if="props.shelfUrl"
                            class="truncate text-xs text-primary"
                        >
                            {{ props.shelfUrl }}
                        </p>
                    </div>
                </div>

                <Switch
                    id="is_public"
                    :model-value="form.is_public"
                    @update:model-value="form.is_public = $event"
                />
            </div>
            <InputError :message="form.errors.is_public" />

            <div class="flex flex-wrap items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    <Loader2
                        v-if="form.processing"
                        class="size-4 animate-spin"
                    />
                    <Save v-else class="size-4" />
                    Simpan gaya rak
                </Button>

                <ShareButton
                    v-if="props.shelfUrl && form.is_public"
                    :url="props.shelfUrl"
                    title="Rak buku saya"
                    label="Bagikan rak"
                />
            </div>
        </form>
    </div>
</template>
