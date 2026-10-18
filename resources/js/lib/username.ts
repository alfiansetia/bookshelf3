import type { FieldRule } from '@/lib/validation';

export type UsernameRules = { min: number; max: number };

/** Rapikan username sama seperti server: trim, huruf kecil, spasi jadi '-'. */
export function normalizeUsername(value: string): string {
    return value.trim().toLowerCase().replace(/ +/g, '-');
}

/** Pesan galat username, atau null bila lolos aturan klien. */
export function usernameError(
    value: string,
    rules: UsernameRules,
): string | null {
    const username = normalizeUsername(value);

    if (username === '') {
        return 'Username wajib diisi karena dipakai sebagai alamat rak kamu.';
    }

    if (username.length < rules.min) {
        return `Username minimal ${rules.min} karakter.`;
    }

    if (username.length > rules.max) {
        return `Username maksimal ${rules.max} karakter.`;
    }

    if (!/^[a-z0-9._-]+$/.test(username)) {
        return 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.';
    }

    if (/^[._-]|[._-]$/.test(username)) {
        return 'Username harus diawali dan diakhiri huruf atau angka.';
    }

    return null;
}

/** Aturan klien untuk field username; hanya menerima nilai berupa teks. */
export function usernameRule(rules: UsernameRules): FieldRule {
    return (value) =>
        usernameError(typeof value === 'string' ? value : '', rules);
}
