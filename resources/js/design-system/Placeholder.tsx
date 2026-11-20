import { isPlaceholder, placeholderLabel } from '@/lib/placeholder';

/**
 * Renders a text that may still be a placeholder. Placeholders are always
 * visibly marked: they can never be mistaken for real company data.
 */
export function Txt({ value, fallback }: { value: string | null | undefined; fallback?: string }) {
    if (value === null || value === undefined || value === '') {
        return <PlaceholderChip label={fallback ?? 'contenuto'} />;
    }
    if (isPlaceholder(value)) {
        return <PlaceholderChip label={placeholderLabel(value)} />;
    }
    return <>{value}</>;
}

export function PlaceholderChip({ label }: { label: string }) {
    return (
        <span className="placeholder-text" title="Contenuto da fornire a cura di Edison">
            <span className="sr-only">Contenuto non ancora disponibile: </span>
            <span aria-hidden className="mr-1 text-[0.7em] font-semibold tracking-wider uppercase">
                Da fornire
            </span>
            {label}
        </span>
    );
}

/** Multi-line text (addresses, opening hours) preserving line breaks. */
export function MultilineTxt({ value, fallback }: { value: string | null | undefined; fallback: string }) {
    if (!value || isPlaceholder(value)) return <Txt value={value} fallback={fallback} />;
    return <span className="whitespace-pre-line">{value}</span>;
}
