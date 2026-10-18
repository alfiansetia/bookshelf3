/**
 * Validasi ringan di sisi klien.
 *
 * Backend tetap satu-satunya penentu sah atau tidaknya sebuah isian; aturan di
 * sini hanya meniru aturan server supaya pengguna dapat umpan balik tanpa
 * menunggu satu putaran request. Karena itu usahakan selalu lebih longgar:
 * lebih baik server yang menolak daripada form memblokir isian yang sah.
 */

export type ValidationErrors = Record<string, string>;

/** Nilai form apa pun: teks, angka, berkas, atau boolean. */
export type FieldValues = Record<string, unknown>;

/**
 * Aturan satu field: kembalikan pesan galat, atau `null` bila isian benar.
 *
 * `values` dipakai aturan yang bergantung pada field lain, misalnya tanggal
 * selesai yang tidak boleh mendahului tanggal mulai.
 */
export type FieldRule = (value: unknown, values: FieldValues) => string | null;

export type ValidationRules = Record<string, FieldRule | FieldRule[]>;

/**
 * Batasi nama field pada tipe form supaya salah tulis langsung ketahuan saat
 * type-check.
 */
export function rulesFor<TValues extends FieldValues>(rules: {
    [K in keyof TValues & string]?: FieldRule | FieldRule[];
}): ValidationRules {
    return rules as ValidationRules;
}

/**
 * Jalankan aturan dan simpan pesan galat pertama untuk setiap field.
 */
export function runRules(
    values: FieldValues,
    rules: ValidationRules,
): ValidationErrors {
    const errors: ValidationErrors = {};

    for (const [field, fieldRules] of Object.entries(rules)) {
        const list = Array.isArray(fieldRules) ? fieldRules : [fieldRules];

        for (const rule of list) {
            const message = rule(values[field], values);

            if (message !== null) {
                errors[field] = message;
                break;
            }
        }
    }

    return errors;
}

/** Field dianggap kosong bila null, undefined, atau hanya berisi spasi. */
export function isBlank(value: unknown): boolean {
    if (value === null || value === undefined) {
        return true;
    }

    return typeof value === 'string' && value.trim() === '';
}

/** Nilai field sebagai teks terpangkas; nilai non-teks dianggap kosong. */
function asText(value: unknown): string {
    return typeof value === 'string' ? value.trim() : '';
}

export function required(message: string): FieldRule {
    return (value) => (isBlank(value) ? message : null);
}

export function maxCharacters(max: number, message: string): FieldRule {
    return (value) => (asText(value).length > max ? message : null);
}

/**
 * Sengaja longgar: hanya menolak alamat yang jelas rusak, misalnya tanpa `@`
 * atau mengandung spasi. Sisanya diputuskan aturan email di server.
 */
export function emailAddress(message: string): FieldRule {
    return (value) => {
        const email = asText(value);

        if (email === '' || /^[^\s@]+@[^\s@]+$/.test(email)) {
            return null;
        }

        return message;
    };
}

/** Bilangan bulat dalam rentang tertentu; isian kosong dilewatkan. */
export function wholeNumber(
    min: number,
    max: number,
    message: string,
): FieldRule {
    return (value) => {
        const raw = asText(value);

        if (raw === '') {
            return null;
        }

        const parsed = Number(raw);

        if (!Number.isInteger(parsed) || parsed < min || parsed > max) {
            return message;
        }

        return null;
    };
}

/** Tanggal `YYYY-MM-DD` yang tidak boleh mendahului tanggal field lain. */
export function notBefore(field: string, message: string): FieldRule {
    return (value, values) => {
        const current = asText(value);
        const other = asText(values[field]);

        if (current === '' || other === '' || current >= other) {
            return null;
        }

        return message;
    };
}

/** Tanggal `YYYY-MM-DD` yang tidak boleh melewati hari ini. */
export function notLaterThanToday(message: string): FieldRule {
    return (value) => {
        const raw = asText(value);

        if (raw === '' || raw <= todayIsoDate()) {
            return null;
        }

        return message;
    };
}

/** Tautan web; nilai tanpa skema dianggap `https://` seperti di server. */
export function webAddress(message: string): FieldRule {
    return (value) => {
        const raw = asText(value);

        if (raw === '') {
            return null;
        }

        try {
            new URL(/^https?:\/\//i.test(raw) ? raw : `https://${raw}`);

            return null;
        } catch {
            return message;
        }
    };
}

/** Tanggal hari ini di zona waktu pengguna, sejalan dengan DatePicker. */
function todayIsoDate(): string {
    const now = new Date();
    const month = `${now.getMonth() + 1}`.padStart(2, '0');
    const day = `${now.getDate()}`.padStart(2, '0');

    return `${now.getFullYear()}-${month}-${day}`;
}
