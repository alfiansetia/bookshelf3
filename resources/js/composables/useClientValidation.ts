import { watch } from 'vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { runRules } from '@/lib/validation';
import type { ValidationErrors, ValidationRules } from '@/lib/validation';

/**
 * Validasi form di klien sebelum request dikirim.
 *
 * Hasilnya ditulis ke `form.errors` supaya komponen InputError yang sudah ada
 * dipakai apa adanya, dan hilang sendiri begitu isian diperbaiki. Server tetap
 * memvalidasi ulang seluruh isian.
 */
export function useClientValidation<TValues extends Record<string, unknown>>(
    form: InertiaForm<TValues>,
    rules: ValidationRules,
): { validate: () => boolean } {
    const fields = Object.keys(rules);

    // Hanya galat yang kita tulis sendiri yang boleh dihapus otomatis, supaya
    // galat dari server (misal "username sudah dipakai") tetap terbaca.
    const clientErrors = new Set<string>();

    // Nama field pada aturan itu runtime, sedangkan tipe form memakai key yang
    // sudah pasti; satu titik cast di sini cukup.
    const applyErrors = (errors: ValidationErrors): void => {
        form.setError(errors as unknown as Parameters<typeof form.setError>[0]);
    };
    const clearField = (field: string): void => {
        (form.clearErrors as unknown as (field: string) => void)(field);
    };

    // Galat yang sudah diperbaiki ikut hilang saat pengguna mengetik.
    watch(form, () => {
        if (clientErrors.size === 0) {
            return;
        }

        const errors = runRules(form.data(), rules);

        fields.forEach((field) => {
            if (!clientErrors.has(field) || errors[field] !== undefined) {
                return;
            }

            clientErrors.delete(field);
            clearField(field);
        });
    });

    return {
        validate(): boolean {
            const errors = runRules(form.data(), rules);

            fields.forEach((field) => {
                if (clientErrors.delete(field)) {
                    clearField(field);
                }
            });

            const entries = Object.entries(errors);

            if (entries.length === 0) {
                return true;
            }

            entries.forEach(([field]) => clientErrors.add(field));
            applyErrors(errors);

            return false;
        },
    };
}
