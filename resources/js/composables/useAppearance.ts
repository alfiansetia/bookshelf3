import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import { CONSENT_CHANGE_EVENT, allowsPreferences } from '@/lib/cookieConsent';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

const appearance = ref<Appearance>('system');

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia(
            '(prefers-color-scheme: dark)',
        );
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle(
            'dark',
            systemTheme === 'dark',
        );
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

/**
 * Buang preferensi tampilan yang tersimpan, misalnya saat pengguna menolak
 * cookie preferensi.
 */
export function clearAppearancePreference(): void {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.removeItem('appearance');

    if (typeof document !== 'undefined') {
        document.cookie = 'appearance=;path=/;max-age=0;SameSite=Lax';
    }
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);

    // Tema yang dipilih sebelum cookie preferensi disetujui baru disimpan
    // setelah pengguna memberi izin.
    window.addEventListener(CONSENT_CHANGE_EVENT, () => {
        persistAppearancePreference(appearance.value);
    });
}

/** Simpan tema terpilih, hanya bila cookie preferensi diizinkan. */
function persistAppearancePreference(value: Appearance): void {
    if (!allowsPreferences()) {
        return;
    }

    localStorage.setItem('appearance', value);
    setCookie('appearance', value);
}

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        const savedAppearance = localStorage.getItem(
            'appearance',
        ) as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        persistAppearancePreference(value);
        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
