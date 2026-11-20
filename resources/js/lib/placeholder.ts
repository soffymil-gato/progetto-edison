/** Marker of content still to be supplied by Edison (see resources/content/site.php). */
export const PLACEHOLDER_MARKER = '[DA FORNIRE';

export function isPlaceholder(value: unknown): boolean {
    return typeof value === 'string' && value.includes(PLACEHOLDER_MARKER);
}

/** Extracts the description from "[DA FORNIRE: description]". */
export function placeholderLabel(value: string): string {
    const match = /\[DA FORNIRE:\s*([^\]]+)\]/.exec(value);
    return match?.[1]?.trim() ?? value;
}
