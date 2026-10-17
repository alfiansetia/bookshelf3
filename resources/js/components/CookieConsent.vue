<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Cookie, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { clearAppearancePreference } from '@/composables/useAppearance';
import {
    CONSENT_CHANGE_EVENT,
    CONSENT_OPEN_EVENT,
    readCookieConsent,
    saveCookieConsent,
} from '@/lib/cookieConsent';
import { cookie as cookiePolicy } from '@/routes/legal';

const mounted = ref(false);
const hasDecided = ref(true);
const settingsOpen = ref(false);
const preferences = ref(true);

const visible = computed(
    () => mounted.value && (!hasDecided.value || settingsOpen.value),
);

function persist(allowPreferences: boolean): void {
    saveCookieConsent(allowPreferences);

    if (!allowPreferences) {
        clearAppearancePreference();
    }

    preferences.value = allowPreferences;
    hasDecided.value = true;
    settingsOpen.value = false;
}

function acceptAll(): void {
    persist(true);
}

function essentialOnly(): void {
    persist(false);
}

function openSettings(): void {
    settingsOpen.value = true;
}

function close(): void {
    settingsOpen.value = false;
}

function onOpenEvent(): void {
    settingsOpen.value = true;
}

function onConsentEvent(): void {
    hasDecided.value = true;
    settingsOpen.value = false;
}

onMounted(() => {
    const consent = readCookieConsent();

    hasDecided.value = consent !== null;
    preferences.value = consent?.preferences ?? true;
    mounted.value = true;

    window.addEventListener(CONSENT_OPEN_EVENT, onOpenEvent);
    window.addEventListener(CONSENT_CHANGE_EVENT, onConsentEvent);
});

onBeforeUnmount(() => {
    window.removeEventListener(CONSENT_OPEN_EVENT, onOpenEvent);
    window.removeEventListener(CONSENT_CHANGE_EVENT, onConsentEvent);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="translate-y-4 opacity-0"
    >
        <aside
            v-if="visible"
            role="region"
            aria-label="Persetujuan cookie"
            class="fixed inset-x-3 bottom-3 z-50 mx-auto max-w-3xl rounded-2xl border border-border/80 bg-card/95 p-5 shadow-lg backdrop-blur supports-[backdrop-filter]:bg-card/85 sm:inset-x-4 sm:bottom-4"
        >
            <div class="flex items-start gap-3">
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                >
                    <Cookie class="size-4.5" />
                </span>

                <div class="min-w-0 flex-1 space-y-1.5">
                    <p class="font-medium text-foreground">
                        Kami memakai cookie
                    </p>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Cookie esensial menjaga sesi dan keamanan akunmu. Cookie
                        preferensi mengingat pilihan tema terang atau gelap.
                        Baca selengkapnya di
                        <Link
                            :href="cookiePolicy()"
                            class="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Kebijakan cookie </Link
                        >.
                    </p>
                </div>

                <Button
                    v-if="hasDecided"
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="-mt-1 -mr-1 shrink-0"
                    aria-label="Tutup pengaturan cookie"
                    @click="close"
                >
                    <X class="size-4" />
                </Button>
            </div>

            <div
                v-if="settingsOpen"
                class="mt-4 space-y-3 rounded-xl border border-border/70 bg-muted/40 p-4"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-0.5">
                        <p class="text-sm font-medium text-foreground">
                            Esensial
                        </p>
                        <p class="text-xs leading-relaxed text-muted-foreground">
                            Sesi masuk, perlindungan CSRF, dan penyimpanan
                            pilihan cookie. Selalu aktif dan tidak bisa
                            dimatikan.
                        </p>
                    </div>
                    <Switch :model-value="true" disabled class="mt-0.5" />
                </div>

                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-0.5">
                        <p class="text-sm font-medium text-foreground">
                            Preferensi
                        </p>
                        <p class="text-xs leading-relaxed text-muted-foreground">
                            Mengingat tema tampilan yang kamu pilih agar tidak
                            perlu diatur ulang setiap kunjungan.
                        </p>
                    </div>
                    <Switch
                        v-model="preferences"
                        class="mt-0.5"
                        aria-label="Izinkan cookie preferensi"
                    />
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <template v-if="settingsOpen">
                    <Button type="button" @click="persist(preferences)">
                        Simpan pilihan
                    </Button>
                    <Button type="button" variant="outline" @click="acceptAll">
                        Terima semua
                    </Button>
                </template>

                <template v-else>
                    <Button type="button" @click="acceptAll">
                        Terima semua
                    </Button>
                    <Button type="button" variant="outline" @click="essentialOnly">
                        Hanya esensial
                    </Button>
                    <Button type="button" variant="ghost" @click="openSettings">
                        Atur
                    </Button>
                </template>
            </div>
        </aside>
    </Transition>
</template>
