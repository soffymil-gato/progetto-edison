import { Search, X } from 'lucide-react';
import type { ReactNode } from 'react';

import { Input } from '@/design-system/Input';

/** Search + filters toolbar of the admin tables. */
export function FilterBar({
    search,
    onSearch,
    searchLabel,
    children,
    active,
    onReset,
}: {
    search: string;
    onSearch: (value: string) => void;
    searchLabel: string;
    children?: ReactNode;
    active: boolean;
    onReset: () => void;
}) {
    return (
        <div className="flex flex-col gap-3 border-b border-line px-4 py-4 sm:px-5 lg:flex-row lg:items-center">
            <div className="relative flex-1">
                <Search
                    className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-fg-subtle"
                    aria-hidden
                />
                <Input
                    type="search"
                    value={search}
                    onChange={(e) => onSearch(e.target.value)}
                    placeholder={searchLabel}
                    aria-label={searchLabel}
                    className="pl-10"
                />
            </div>
            <div className="grid gap-3 sm:grid-cols-2 lg:flex lg:items-center">{children}</div>
            {active && (
                <button
                    type="button"
                    onClick={onReset}
                    className="inline-flex items-center gap-1.5 self-start rounded-lg px-2 py-2 text-sm font-medium text-fg-muted hover:text-brand-700 lg:self-auto"
                >
                    <X className="size-4" aria-hidden />
                    Azzera filtri
                </button>
            )}
        </div>
    );
}
