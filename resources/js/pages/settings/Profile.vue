<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { AtSign, Check, Loader2, Save, X } from '@lucide/vue';
import CoverUploadField from '@/components/bookshelf/CoverUploadField.vue';
import ShareButton from '@/components/bookshelf/ShareButton.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { edit, update } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Profil', href: edit() }],
    },
});

const props = defineProps<{
    profile: {
        username: string | null;
        tagline: string | null;
        bio: string | null;
        avatar_url: string | null;
    };
    shelfUrl: string | null;
    availabilityUrl: string;
    usernameRules: { min: number; max: number };
    limits: { tagline: number; bio: number };
    avatarMaxKilobytes: number;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    username: props.profile.username ?? '',
    tagline: props.profile.tagline ?? '',
    bio: props.profile.bio ?? '',
    avatar: null as File | null,
});

const checking = ref(false);
const usernameStatus = ref<'idle' | 'available' | 'taken'>('idle');
const usernameMessage = ref<string | null>(null);

async function checkUsername(value: string): Promise<void> {
    if (value.length < props.usernameRules.min) {
        usernameStatus.value = 'idle';
        usernameMessage.value = null;

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

        const data = (await response.json()) as {
            available: boolean;
            message: string;
        };

        usernameStatus.value = data.available ? 'available' : 'taken';
        usernameMessage.value = data.message;
    } catch {
        usernameStatus.value = 'idle';
        usernameMessage.value = null;
    } finally {
        checking.value = false;
    }
}

const checkUsernameDebounced = useDebounceFn(
    (value: string) => void checkUsername(value),
    400,
);

function onUsernameInput(): void {
    usernameStatus.value = 'idle';
    usernameMessage.value = null;
    checkUsernameDebounced(form.username);
}

function submit(): void {
    form.post(update.url(), {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Profil" />

    <h1 class="sr-only">Pengaturan profil</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profil publik"
            description="Nama, foto, dan deskripsi singkat yang muncul di rak publikmu."
        />

        <form class="space-y-6" novalidate @submit.prevent="submit">
            <CoverUploadField
                v-model="form.avatar"
                aspect="avatar"
                label="Foto profil"
                :existing-url="props.profile.avatar_url"
                :max-kilobytes="props.avatarMaxKilobytes"
                hint="JPG, PNG, atau WEBP. Maksimal 2 MB."
            />
            <InputError :message="form.errors.avatar" />

            <div class="grid gap-2">
                <Label for="name">Nama tampilan</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autocomplete="name"
                    placeholder="Nama lengkap"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="username">Username rak</Label>
                <div class="relative">
                    <AtSign
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        autocapitalize="none"
                        spellcheck="false"
                        :maxlength="props.usernameRules.max"
                        class="pr-10 pl-9"
                        placeholder="usernamekamu"
                        @input="onUsernameInput"
                    />

                    <span
                        class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2"
                    >
                        <Loader2
                            v-if="checking"
                            class="size-4 animate-spin text-muted-foreground"
                        />
                        <Check
                            v-else-if="usernameStatus === 'available'"
                            class="size-4 text-emerald-500"
                        />
                        <X
                            v-else-if="usernameStatus === 'taken'"
                            class="size-4 text-destructive"
                        />
                    </span>
                </div>

                <p
                    :class="
                        cn(
                            'text-xs',
                            usernameStatus === 'available'
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : usernameStatus === 'taken'
                                  ? 'text-destructive'
                                  : 'text-muted-foreground',
                        )
                    "
                >
                    {{
                        usernameMessage ??
                        'Username ini dipakai sebagai alamat rak publikmu.'
                    }}
                </p>
                <p v-if="props.shelfUrl" class="truncate text-xs text-primary">
                    {{ props.shelfUrl }}
                </p>
                <InputError :message="form.errors.username" />
            </div>

            <div class="grid gap-2">
                <Label for="tagline">Tagline</Label>
                <Input
                    id="tagline"
                    v-model="form.tagline"
                    type="text"
                    :maxlength="props.limits.tagline"
                    placeholder="Contoh: Pembaca novel fiksi ilmiah"
                />
                <p class="text-xs text-muted-foreground">
                    Kalimat pendek yang muncul di bawah namamu.
                </p>
                <InputError :message="form.errors.tagline" />
            </div>

            <div class="grid gap-2">
                <Label for="bio">Tentang kamu</Label>
                <Textarea
                    id="bio"
                    v-model="form.bio"
                    rows="4"
                    :maxlength="props.limits.bio"
                    placeholder="Ceritakan selera bacamu atau buku yang sedang diburu."
                />
                <p class="text-xs text-muted-foreground">
                    {{ form.bio.length }}/{{ props.limits.bio }} karakter
                </p>
                <InputError :message="form.errors.bio" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Alamat email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    <Loader2
                        v-if="form.processing"
                        class="size-4 animate-spin"
                    />
                    <Save v-else class="size-4" />
                    Simpan profil
                </Button>

                <ShareButton
                    v-if="props.shelfUrl"
                    :url="props.shelfUrl"
                    title="Rak buku saya"
                    label="Bagikan rak"
                />

                <span
                    v-if="form.recentlySuccessful"
                    class="text-sm text-emerald-600 dark:text-emerald-400"
                >
                    Tersimpan.
                </span>
            </div>
        </form>
    </div>

    <DeleteUser />
</template>
