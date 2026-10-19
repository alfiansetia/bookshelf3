import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome' ||
                name === 'Explore' ||
                name.startsWith('public/') ||
                name.startsWith('legal/') ||
                name.startsWith('errors/'):
                return PublicLayout;
            case name.startsWith('auth/') || name.startsWith('onboarding/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

// Daftarkan service worker PWA. Registrasi tetap jalan di dev supaya PWA bisa
// diuji di localhost; strategi cache di sw.js hanya menyentuh aset same-origin
// sehingga tidak mengganggu HMR Vite. Kegagalan di sini tidak boleh mengganggu
// jalannya aplikasi.
if ('serviceWorker' in navigator) {
    const registerServiceWorker = () => {
        void navigator.serviceWorker
            .register(`/sw.js?v=${__SW_VERSION__}`, {
                scope: '/',
                updateViaCache: 'none',
            })
            .catch(() => {});
    };

    if (document.readyState === 'complete') {
        registerServiceWorker();
    } else {
        window.addEventListener('load', registerServiceWorker, { once: true });
    }
}
