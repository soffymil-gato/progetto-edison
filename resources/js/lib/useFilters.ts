import { router } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';

/**
 * Server-side filters bound to the query string. Text search is debounced.
 */
export function useFilters<T extends Record<string, string>>(url: string, initial: T) {
    const [values, setValues] = useState<T>(initial);
    const latest = useRef<T>(initial);
    const timer = useRef<number | undefined>(undefined);

    const apply = useCallback(
        (next: T) => {
            const query = Object.fromEntries(Object.entries(next).filter(([, value]) => value !== ''));
            router.get(url, query, { preserveState: true, preserveScroll: true, replace: true });
        },
        [url],
    );

    const set = useCallback(
        (key: keyof T, value: string, debounce = false) => {
            const next = { ...latest.current, [key]: value };
            latest.current = next;
            setValues(next);
            window.clearTimeout(timer.current);
            if (debounce) {
                timer.current = window.setTimeout(() => apply(next), 350);
            } else {
                apply(next);
            }
        },
        [apply],
    );

    const reset = useCallback(() => {
        const cleared = Object.fromEntries(Object.keys(latest.current).map((key) => [key, ''])) as T;
        latest.current = cleared;
        setValues(cleared);
        window.clearTimeout(timer.current);
        apply(cleared);
    }, [apply]);

    const active = Object.entries(values).some(([key, value]) => key !== 'sort' && value !== '');

    return { values, set, reset, active };
}
