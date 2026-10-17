const units = ['B', 'KB', 'MB', 'GB'];

/**
 * Ukuran berkas yang enak dibaca, contoh: "1,4 MB".
 */
export function formatSize(bytes: number): string {
    if (!Number.isFinite(bytes) || bytes <= 0) {
        return '0 B';
    }

    const exponent = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** exponent;
    const formatted = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: value >= 10 || exponent === 0 ? 0 : 1,
    }).format(value);

    return `${formatted} ${units[exponent]}`;
}
