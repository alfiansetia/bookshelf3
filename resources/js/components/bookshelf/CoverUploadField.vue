<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Image as ImageIcon, Trash2, Upload } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatSize } from '@/lib/upload';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        existingUrl?: string | null;
        accept?: string;
        maxKilobytes?: number;
        aspect?: 'cover' | 'avatar';
        label?: string;
        hint?: string;
        class?: string;
    }>(),
    {
        existingUrl: null,
        accept: 'image/jpeg,image/png,image/webp',
        maxKilobytes: 3072,
        aspect: 'cover',
        label: 'Sampul depan buku',
        hint: undefined,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
    (e: 'remove'): void;
}>();

const input = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const localError = ref<string | null>(null);

const shownUrl = computed(() => previewUrl.value ?? props.existingUrl ?? null);

watch(
    () => props.modelValue,
    (file) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }

        if (file) {
            previewUrl.value = URL.createObjectURL(file);
        }
    },
);

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

function pick(): void {
    input.value?.click();
}

/** Format berkas yang diterima, dipakai untuk pesan galat. */
function acceptedFormats(): string {
    return props.accept
        .split(',')
        .map((mime) => mime.trim().toLowerCase())
        .filter((mime) => mime !== '')
        .map((mime) => {
            const [, subtype = mime] = mime.split('/');

            return subtype === 'jpeg' ? 'JPG' : subtype.toUpperCase();
        })
        .join(', ');
}

function isAcceptedType(type: string): boolean {
    return props.accept
        .split(',')
        .map((mime) => mime.trim().toLowerCase())
        .includes(type.toLowerCase());
}

function onFileChange(event: Event): void {
    localError.value = null;

    const files = (event.target as HTMLInputElement).files;
    const file = files?.[0] ?? null;

    if (!file) {
        return;
    }

    if (!isAcceptedType(file.type)) {
        localError.value = `Format berkas harus ${acceptedFormats()}.`;
        emit('update:modelValue', null);

        return;
    }

    if (file.size > props.maxKilobytes * 1024) {
        localError.value = `Ukuran maksimal ${props.maxKilobytes / 1024} MB.`;
        emit('update:modelValue', null);

        return;
    }

    emit('update:modelValue', file);
}

function clear(): void {
    localError.value = null;

    if (input.value) {
        input.value.value = '';
    }

    emit('update:modelValue', null);
    emit('remove');
}
</script>

<template>
    <div
        :class="
            cn('flex flex-col gap-3 sm:flex-row sm:items-start', props.class)
        "
    >
        <div
            :class="
                cn(
                    'relative flex shrink-0 items-center justify-center overflow-hidden border border-dashed border-border bg-muted/40',
                    props.aspect === 'cover'
                        ? 'aspect-2/3 w-32 rounded-lg'
                        : 'size-24 rounded-2xl',
                )
            "
        >
            <img
                v-if="shownUrl"
                :src="shownUrl"
                alt="Pratinjau"
                class="h-full w-full object-cover"
            />
            <ImageIcon v-else class="size-7 text-muted-foreground" />
        </div>

        <div class="flex-1 space-y-2">
            <p class="text-sm font-medium text-foreground">{{ props.label }}</p>

            <p class="text-xs text-muted-foreground">
                {{
                    props.hint ??
                    `JPG, PNG, atau WEBP. Maksimal ${props.maxKilobytes / 1024} MB.`
                }}
            </p>

            <div class="flex flex-wrap items-center gap-2">
                <Button type="button" variant="outline" size="sm" @click="pick">
                    <Upload class="size-4" />
                    {{ shownUrl ? 'Ganti gambar' : 'Pilih gambar' }}
                </Button>

                <Button
                    v-if="shownUrl"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="text-muted-foreground"
                    @click="clear"
                >
                    <Trash2 class="size-4" />
                    Hapus
                </Button>

                <span
                    v-if="props.modelValue"
                    class="text-xs text-muted-foreground"
                >
                    {{ props.modelValue.name }} ·
                    {{ formatSize(props.modelValue.size) }}
                </span>
            </div>

            <p v-if="localError" class="text-xs font-medium text-destructive">
                {{ localError }}
            </p>

            <input
                ref="input"
                type="file"
                :accept="props.accept"
                class="hidden"
                @change="onFileChange"
            />
        </div>
    </div>
</template>
