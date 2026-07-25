/**
 * Domain helpers bridging the Laravel backend enums/data to the official
 * PescaDex design language (labels, rarity palette, formatting).
 */

export type BackendRarity = 'common' | 'uncommon' | 'rare' | 'legendary';
export type OfficialRarity = 'comun' | 'pocoComun' | 'rara' | 'legendaria';

const RARITY_KEY: Record<BackendRarity, OfficialRarity> = {
    common: 'comun',
    uncommon: 'pocoComun',
    rare: 'rara',
    legendary: 'legendaria',
};

export interface RarityMeta {
    label: string;
    short: string;
    color: string;
    ink: string;
    /** CSS variable pair for the FishPhoto stripe placeholder. */
    photo: [string, string];
}

export const RARITY_META: Record<OfficialRarity, RarityMeta> = {
    comun: {
        label: 'Común',
        short: 'COMÚN',
        color: 'var(--r-comun)',
        ink: '#ffffff',
        photo: ['var(--photo-comun-a)', 'var(--photo-comun-b)'],
    },
    pocoComun: {
        label: 'Poco común',
        short: 'POCO C.',
        color: 'var(--r-poco)',
        ink: '#06281c',
        photo: ['var(--photo-poco-a)', 'var(--photo-poco-b)'],
    },
    rara: {
        label: 'Rara',
        short: 'RARA',
        color: 'var(--r-rara)',
        ink: '#ffffff',
        photo: ['var(--photo-rara-a)', 'var(--photo-rara-b)'],
    },
    legendaria: {
        label: 'Legendaria',
        short: 'LEGEND.',
        color: 'var(--r-legend)',
        ink: '#3a1e00',
        photo: ['var(--photo-legend-a)', 'var(--photo-legend-b)'],
    },
};

/** Normalise a backend rarity to the official key (defaults to común). */
export function rarityKey(rarity: string | null | undefined): OfficialRarity {
    return RARITY_KEY[(rarity as BackendRarity)] ?? 'comun';
}

export function rarityMeta(rarity: string | null | undefined): RarityMeta {
    return RARITY_META[rarityKey(rarity)];
}

export const METHOD_LABELS: Record<string, string> = {
    spinning: 'Spinning',
    fly: 'Mosca',
    bait_casting: 'Bait Casting',
    trolling: 'Trolling',
    other: 'Otro',
};

export const WATER_LABELS: Record<string, string> = {
    river: 'Río',
    lake: 'Lago',
    sea: 'Mar',
    reservoir: 'Embalse',
    other: 'Otro',
};

export function methodLabel(method: string | null | undefined): string {
    return METHOD_LABELS[method ?? ''] ?? method ?? '—';
}

export function waterLabel(water: string | null | undefined): string {
    return WATER_LABELS[water ?? ''] ?? water ?? '—';
}

const MONTHS_ES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

/** "12 may 2026" or "12 may 2026 · 07:40" when withTime. */
export function formatDateEs(iso: string | null | undefined, withTime = false): string {
    if (!iso) {
return '';
}

    const d = new Date(iso);

    if (Number.isNaN(d.getTime())) {
return '';
}

    const base = `${d.getDate()} ${MONTHS_ES[d.getMonth()]} ${d.getFullYear()}`;

    if (!withTime) {
return base;
}

    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');

    return `${base} · ${hh}:${mm}`;
}

/** "hace 2 h", "hace 3 días"… relative to now. */
export function timeAgo(iso: string | null | undefined): string {
    if (!iso) {
return '';
}

    const d = new Date(iso);

    if (Number.isNaN(d.getTime())) {
return '';
}

    const secs = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));

    if (secs < 60) {
return 'hace un momento';
}

    const mins = Math.floor(secs / 60);

    if (mins < 60) {
return `hace ${mins} min`;
}

    const hours = Math.floor(mins / 60);

    if (hours < 24) {
return `hace ${hours} h`;
}

    const days = Math.floor(hours / 24);

    if (days < 7) {
return `hace ${days} ${days === 1 ? 'día' : 'días'}`;
}

    const weeks = Math.floor(days / 7);

    if (weeks < 5) {
return `hace ${weeks} sem`;
}

    const months = Math.floor(days / 30);

    if (months < 12) {
return `hace ${months} ${months === 1 ? 'mes' : 'meses'}`;
}

    return `hace ${Math.floor(days / 365)} años`;
}

/** Format a guaraní amount as e.g. "₲ 850.000" (no decimals). */
export function formatPrice(value: number | string | null | undefined): string {
    const n = Number(value ?? 0);

    return new Intl.NumberFormat('es-PY', {
        style: 'currency',
        currency: 'PYG',
        maximumFractionDigits: 0,
    }).format(n);
}

/** Resolve a storage-relative path to a public URL, or null if absent. */
export function storageUrl(path: string | null | undefined): string | null {
    if (!path) {
return null;
}

    if (path.startsWith('http') || path.startsWith('/')) {
return path;
}

    return `/storage/${path}`;
}

export function levelTitle(level: number): string {
    if (level >= 30) {
return 'Leyenda de la pesca';
}

    if (level >= 20) {
return 'Maestro pescador';
}

    if (level >= 12) {
return 'Pescador experto';
}

    if (level >= 6) {
return 'Pescador avanzado';
}

    if (level >= 3) {
return 'Aficionado';
}

    return 'Principiante';
}
