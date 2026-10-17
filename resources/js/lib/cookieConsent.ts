/**
 * Persetujuan cookie (esensial + preferensi).
 *
 * Pilihan pengguna disimpan pada cookie pihak pertama `cookie_consent` selama
 * satu tahun. Cookie sesi dan CSRF selalu aktif karena dibutuhkan aplikasi,
 * sedangkan preferensi (tema tampilan) hanya disimpan bila disetujui.
 */

export type CookieConsent = {
    version: number;
    essential: true;
    preferences: boolean;
    decidedAt: string;
};

export const CONSENT_COOKIE = 'cookie_consent';

export const CONSENT_VERSION = 1;

/** Dipicu setiap kali pilihan cookie disimpan atau berubah. */
export const CONSENT_CHANGE_EVENT = 'rakbuku:cookie-consent-change';

/** Dipicu saat pengguna membuka kembali pengaturan cookie. */
export const CONSENT_OPEN_EVENT = 'rakbuku:cookie-consent-open';

const MAX_AGE_SECONDS = 60 * 60 * 24 * 365;

function readRawCookie(name: string): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(
        new RegExp(`(?:^|; )${name.replace(/\./g, '\\.')}=([^;]*)`),
    );

    return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Pilihan cookie yang tersimpan, atau null bila pengguna belum memutuskan.
 */
export function readCookieConsent(): CookieConsent | null {
    const raw = readRawCookie(CONSENT_COOKIE);

    if (raw === null) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw) as Partial<CookieConsent>;

        if (parsed.version !== CONSENT_VERSION) {
            return null;
        }

        return {
            version: CONSENT_VERSION,
            essential: true,
            preferences: parsed.preferences === true,
            decidedAt:
                typeof parsed.decidedAt === 'string'
                    ? parsed.decidedAt
                    : new Date().toISOString(),
        };
    } catch {
        return null;
    }
}

/**
 * Simpan pilihan cookie dan beri tahu komponen lain.
 */
export function saveCookieConsent(preferences: boolean): CookieConsent {
    const consent: CookieConsent = {
        version: CONSENT_VERSION,
        essential: true,
        preferences,
        decidedAt: new Date().toISOString(),
    };

    if (typeof document !== 'undefined') {
        document.cookie = `${CONSENT_COOKIE}=${encodeURIComponent(
            JSON.stringify(consent),
        )};path=/;max-age=${MAX_AGE_SECONDS};SameSite=Lax`;

        window.dispatchEvent(
            new CustomEvent<CookieConsent>(CONSENT_CHANGE_EVENT, {
                detail: consent,
            }),
        );
    }

    return consent;
}

/**
 * Apakah pengguna sudah pernah memilih.
 */
export function hasCookieConsent(): boolean {
    return readCookieConsent() !== null;
}

/**
 * Apakah cookie preferensi (tema tampilan) boleh disimpan.
 */
export function allowsPreferences(): boolean {
    return readCookieConsent()?.preferences === true;
}

/**
 * Buka kembali panel pengaturan cookie.
 */
export function openCookieConsent(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(new Event(CONSENT_OPEN_EVENT));
}
