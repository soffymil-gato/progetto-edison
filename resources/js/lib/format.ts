/** Dates are stored in UTC and always displayed in the Italian time zone. */
const TIME_ZONE = 'Europe/Rome';

const dateTimeFormatter = new Intl.DateTimeFormat('it-IT', {
    timeZone: TIME_ZONE,
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const dateFormatter = new Intl.DateTimeFormat('it-IT', {
    timeZone: TIME_ZONE,
    day: '2-digit',
    month: 'long',
    year: 'numeric',
});

const relativeFormatter = new Intl.RelativeTimeFormat('it-IT', { numeric: 'auto' });

export function formatDateTime(iso: string | null | undefined): string {
    return iso ? dateTimeFormatter.format(new Date(iso)) : '—';
}

export function formatDate(iso: string | null | undefined): string {
    return iso ? dateFormatter.format(new Date(iso)) : '—';
}

export function formatRelative(iso: string | null | undefined, now: Date = new Date()): string {
    if (!iso) return '—';
    const seconds = Math.round((new Date(iso).getTime() - now.getTime()) / 1000);
    const units: [Intl.RelativeTimeFormatUnit, number][] = [
        ['year', 31_536_000],
        ['month', 2_592_000],
        ['day', 86_400],
        ['hour', 3_600],
        ['minute', 60],
    ];
    for (const [unit, size] of units) {
        if (Math.abs(seconds) >= size) return relativeFormatter.format(Math.round(seconds / size), unit);
    }
    return 'adesso';
}

export function formatNumber(value: number): string {
    return new Intl.NumberFormat('it-IT').format(value);
}
