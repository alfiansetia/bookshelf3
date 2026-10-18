<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Save } from '@lucide/vue';
import SocialIcon from '@/components/bookshelf/SocialIcon.vue';
import SocialLinks from '@/components/bookshelf/SocialLinks.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useClientValidation } from '@/composables/useClientValidation';
import { webAddress } from '@/lib/validation';
import type { FieldRule, ValidationRules } from '@/lib/validation';
import { edit as editSocials, update } from '@/routes/socials';
import type { SocialPlatformOption, SocialProfile } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tautan sosial', href: editSocials() }],
    },
});

const props = defineProps<{
    platforms: SocialPlatformOption[];
    links: Record<string, string | null>;
    profiles: SocialProfile[];
    shelfUrl: string | null;
}>();

const form = useForm<Record<string, string>>(
    Object.fromEntries(
        props.platforms.map((platform) => [
            `social_${platform.value}`,
            props.links[`social_${platform.value}`] ?? '',
        ]),
    ),
);

const hasProfiles = computed(() => props.profiles.length > 0);

/**
 * Rapikan username media sosial sama seperti server: buang tanda `@` di depan,
 * dan bila yang ditempel berupa URL profil, ambil segmen pertama path-nya.
 */
function normalizeHandle(value: string): string {
    const trimmed = value.trim();

    if (!/^https?:\/\//i.test(trimmed)) {
        return trimmed.replace(/^@+/, '');
    }

    let path = '';

    try {
        path = new URL(trimmed).pathname;
    } catch {
        path = '';
    }

    const segment = path.replace(/^\/+|\/+$/g, '').split('/')[0];

    return (segment === '' ? trimmed : segment).replace(/^@+/, '');
}

function socialRule(platform: SocialPlatformOption): FieldRule {
    if (!platform.username_only) {
        return webAddress(
            `Alamat ${platform.label} harus berupa tautan yang valid.`,
        );
    }

    const message = `Username ${platform.label} hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.`;

    return (value) => {
        const handle = normalizeHandle(typeof value === 'string' ? value : '');

        if (handle === '') {
            return null;
        }

        return /^[A-Za-z0-9._-]+$/.test(handle) ? null : message;
    };
}

// Nilai form berupa record terbuka, jadi aturan dibangun dari daftar platform.
const validationRules: ValidationRules = Object.fromEntries(
    props.platforms.map((platform) => [
        `social_${platform.value}`,
        socialRule(platform),
    ]),
);

const { validate } = useClientValidation(form, validationRules);

function submit(): void {
    if (!validate()) {
        return;
    }

    form.patch(update.url(), { preserveScroll: true });
}
</script>

<template>
    <Head title="Tautan sosial" />

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Tautan sosial"
            description="Cukup tempel username-nya. Tautan lengkap akan dibuat otomatis."
        />

        <div
            v-if="hasProfiles"
            class="space-y-3 rounded-2xl border border-border/70 bg-card p-4"
        >
            <p class="text-sm font-medium text-foreground">
                Yang sudah tampil di rakmu
            </p>
            <SocialLinks :links="props.profiles" show-labels />
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="space-y-4">
                <div
                    v-for="platform in props.platforms"
                    :key="platform.value"
                    class="grid gap-2"
                >
                    <Label
                        :for="`social_${platform.value}`"
                        class="flex items-center gap-2"
                    >
                        <SocialIcon
                            :platform="platform.value"
                            class="size-4 text-muted-foreground"
                        />
                        {{ platform.label }}
                    </Label>

                    <Input
                        :id="`social_${platform.value}`"
                        v-model="form[`social_${platform.value}`]"
                        type="text"
                        autocomplete="off"
                        autocapitalize="none"
                        spellcheck="false"
                        :maxlength="platform.max_length"
                        :placeholder="platform.placeholder"
                    />

                    <p class="text-xs text-muted-foreground">
                        {{
                            platform.username_only
                                ? 'Tulis username saja, tanpa tanda @.'
                                : 'Tulis alamat lengkap situsmu.'
                        }}
                    </p>
                    <InputError
                        :message="form.errors[`social_${platform.value}`]"
                    />
                </div>
            </div>

            <Button type="submit" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                <Save v-else class="size-4" />
                Simpan tautan
            </Button>
        </form>
    </div>
</template>
