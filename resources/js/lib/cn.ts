import clsx, { type ClassValue } from 'clsx';

/**
 * Joins conditional class names. It does not resolve conflicting Tailwind
 * utilities (no tailwind-merge): a `className` that overrides a component's base
 * class of the same property (e.g. `hidden` against `inline-flex`) wins only if
 * Tailwind emits it later. Use a variant instead, e.g. `max-sm:hidden`.
 */
export function cn(...values: ClassValue[]): string {
    return clsx(values);
}
