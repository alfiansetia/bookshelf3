<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Check, Loader2, Sparkles, X } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { update } from '@/routes/onboarding/username';

defineOptions({
    layout: {
        title: 'Pilih username rak',
        description:
            'Username ini jadi alamat rak publikmu, misalnya namamu.example.com.',
    },
});

const props = defineProps<{
    suggestion: string;
    rules: { min: number; max: number };
    availabilityUrl: string;
}>();

const form = useForm({
    username: props.suggestion,
});

const checking = ref(false);
const status = ref<'idle' | 'available' | 'taken' | 'invalid'>('idle');
const message = ref<string | null>(null);

const preview = computed(() => {
    const origin = typeof window === 'undefined' ? '' : window.location.origin;

    return `${origin}/${form.username || 'username'}`;
});

async function check(value: string): Promise<void> {
    if (value.length < props.rules.min) {
        status.value = 'idle';
        message.value = null;

        return;
    }

    checking.value = true;

    try {
        const url = new URL(props.availabilityUrl, window.location.origin);
        url.searchParams.set('username', value);

        const response = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Tidak dapat memeriksa username.');
        }

        const data = (await response.json()) as {
            available: boolean;
            message: string;
        };

        status.value = data.available ? 'available' : 'taken';
        message.value = data.message;
    } catch {
        status.value = 'invalid';
        message.value = 'Gagal memeriksa username, coba lagi sebentar.';
    } finally {
        checking.value = false;
    }
}

const checkDebounced = useDebounceFn((value: string) => void check(value), 400);

watch(
    () => form.username,
    (value) => {
        status.value = 'idle';
        message.value = null;
        checkDebounced(value);
    },
);

function submit(): void {
    form.put(update.url(), { preserveScroll: true });
}
</script>

<template>
    <Head title="Pilih username" />

    <form class="space-y-5" novalidate @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="username">Username rak</Label>
            <div class="relative">
                <Input
                    id="username"
                    v-model="form.username"
                    type="text"
                    required
                    v-focus
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    :placeholder="props.suggestion"
                    :maxlength="props.rules.max"
                    class="pr-10"
                />

                <span
                    class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2"
                >
                    <Loader2
                        v-if="checking"
                        class="size-4 animate-spin text-muted-foreground"
                    />
                    <Check
                        v-else-if="status === 'available'"
                        class="size-4 text-emerald-500"
                    />
                    <X
                        v-else-if="status === 'taken' || status === 'invalid'"
                        class="size-4 text-destructive"
                    />
                </span>
            </div>

            <p
                :class="
                    cn(
                        'text-xs',
                        status === 'available'
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : status === 'taken' || status === 'invalid'
                              ? 'text-destructive'
                              : 'text-muted-foreground',
                    )
                "
            >
                {{
                    message ??
                    `${props.rules.min}-${props.rules.max} karakter, huruf kecil, angka, titik, garis bawah, atau tanda hubung.`
                }}
            </p>
            <InputError :message="form.errors.username" />

            <p
                class="truncate rounded-lg bg-muted px-3 py-2 text-xs text-muted-foreground"
            >
                Rak kamu akan ada di:
                <span class="text-foreground">{{ preview }}</span>
            </p>
        </div>

        <Button
            type="submit"
            class="w-full"
            :disabled="form.processing || status === 'taken'"
        >
            <Loader2 v-if="form.processing" class="size-4 animate-spin" />
            <Sparkles v-else class="size-4" />
            Aktifkan rak saya
        </Button>
    </form>
</template>
