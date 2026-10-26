<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, Link2, Loader2, Lock, Palette, Save } from '@lucide/vue';
import EmptyState from '@/components/bookshelf/EmptyState.vue';
import ShelfRenderer from '@/components/bookshelf/shelves/ShelfRenderer.vue';
import ShelfThemePicker from '@/components/bookshelf/ShelfThemePicker.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { edit as editShelf, update } from '@/routes/shelf';
import type {
    ShelfBook,
    ShelfSummary,
    ShelfTheme,
    ShelfThemeOption,
    ShelfVisibility,
    ShelfVisibilityOption,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Gaya rak', href: editShelf() }],
    },
});

const props = defineProps<{
    themes: ShelfThemeOption[];
    selectedTheme: ShelfTheme;
    visibility: ShelfVisibility;
    visibilityOptions: ShelfVisibilityOption[];
    isPublic: boolean;
    shelfUrl: string | null;
    username: string | null;
    stats: ShelfSummary;
    previewBooks: ShelfBook[];
}>();

const form = useForm({
    theme: props.selectedTheme,
    visibility: props.visibility,
});

const activeTheme = computed(
    () =>
        props.themes.find((theme) => theme.value === form.theme) ??
        props.themes[0],
);

const visibilityIcons: Record<ShelfVisibility, typeof Eye> = {
    public: Eye,
    unlisted: Link2,
    private: Lock,
};

const activeVisibility = computed(
    () =>
        props.visibilityOptions.find(
            (option) => option.value === form.visibility,
        ) ?? props.visibilityOptions[0],
);

function chooseTheme(theme: ShelfTheme): void {
    form.theme = theme;
}

function chooseVisibility(value: ShelfVisibility): void {
    form.visibility = value;
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

            <div class="space-y-3">
                <p class="text-sm font-medium text-foreground">
                    Siapa yang boleh melihat rak ini?
                </p>

                <div class="grid gap-2 sm:grid-cols-3">
                    <button
                        v-for="option in props.visibilityOptions"
                        :key="option.value"
                        type="button"
                        :class="
                            cn(
                                'flex flex-col gap-2 rounded-2xl border p-4 text-left transition-colors',
                                form.visibility === option.value
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                    : 'border-border/70 bg-card hover:border-primary/50',
                            )
                        "
                        @click="chooseVisibility(option.value)"
                    >
                        <span
                            :class="
                                cn(
                                    'flex size-9 items-center justify-center rounded-xl',
                                    form.visibility === option.value
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground',
                                )
                            "
                        >
                            <component
                                :is="visibilityIcons[option.value]"
                                class="size-4"
                            />
                        </span>
                        <span class="space-y-1">
                            <span
                                class="block text-sm font-medium text-foreground"
                            >
                                {{ option.label }}
                            </span>
                            <span
                                class="block text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ option.description }}
                            </span>
                        </span>
                    </button>
                </div>
                <InputError :message="form.errors.visibility" />

                <p
                    v-if="props.shelfUrl && form.visibility !== 'private'"
                    class="truncate text-xs text-primary"
                >
                    {{ props.shelfUrl }}
                </p>
                <p
                    v-else-if="activeVisibility"
                    class="text-xs text-muted-foreground"
                >
                    {{ activeVisibility.description }}
                </p>
            </div>

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
                    v-if="props.shelfUrl && form.visibility !== 'private'"
                    :url="props.shelfUrl"
                    title="Rak buku saya"
                    label="Bagikan rak"
                />
            </div>
        </form>
    </div>
</template>
