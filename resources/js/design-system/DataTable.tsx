import { router } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-react';
import { type ReactNode, useEffect, useState } from 'react';

import { cn } from '@/lib/cn';

import { TableSkeleton } from './Skeleton';

export type Column<T> = {
    key: string;
    header: string;
    cell: (row: T) => ReactNode;
    /** Server-side sort key (e.g. "name"); omit for non sortable columns. */
    sort?: string;
    className?: string;
    /** Hidden in the stacked mobile layout. */
    hideOnMobile?: boolean;
};

/** Tracks Inertia navigations to display skeleton rows while server data loads. */
function useNavigating(): boolean {
    const [navigating, setNavigating] = useState(false);
    useEffect(() => {
        const offStart = router.on('start', (event) => {
            if (event.detail.visit.method === 'get') setNavigating(true);
        });
        const offFinish = router.on('finish', () => setNavigating(false));
        return () => {
            offStart();
            offFinish();
        };
    }, []);
    return navigating;
}

/**
 * Server-driven table: sorting, filtering and pagination happen on the server.
 * On small screens rows become cards so that no horizontal scroll is needed.
 */
export function DataTable<T>({
    columns,
    rows,
    rowKey,
    sort,
    onSort,
    empty,
    caption,
    onRowClick,
}: {
    columns: Column<T>[];
    rows: T[];
    rowKey: (row: T) => string | number;
    sort?: string;
    onSort?: (sort: string) => void;
    empty: ReactNode;
    caption: string;
    onRowClick?: (row: T) => void;
}) {
    const navigating = useNavigating();

    if (navigating) return <TableSkeleton columns={Math.min(columns.length, 5)} />;
    if (rows.length === 0) return <>{empty}</>;

    return (
        <>
            {/* Desktop / tablet */}
            <div className="hidden md:block">
                <table className="w-full text-left text-sm">
                    <caption className="sr-only">{caption}</caption>
                    <thead>
                        <tr className="border-b border-line bg-canvas/60">
                            {columns.map((column) => (
                                <th key={column.key} scope="col" className={cn('px-5 py-3 text-xs font-semibold tracking-wide text-fg-subtle uppercase', column.className)}>
                                    {column.sort && onSort ? <SortButton column={column} sort={sort} onSort={onSort} /> : column.header}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-line">
                        {rows.map((row) => (
                            <tr
                                key={rowKey(row)}
                                onClick={onRowClick ? () => onRowClick(row) : undefined}
                                className={cn('transition-colors', onRowClick && 'cursor-pointer hover:bg-brand-50/50')}
                            >
                                {columns.map((column) => (
                                    <td key={column.key} className={cn('px-5 py-3.5 align-middle text-fg', column.className)}>
                                        {column.cell(row)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Mobile: stacked cards */}
            <ul className="divide-y divide-line md:hidden" aria-label={caption}>
                {rows.map((row) => (
                    <li key={rowKey(row)} className="px-4 py-4">
                        <dl className="grid grid-cols-[minmax(6.5rem,auto)_1fr] gap-x-3 gap-y-1.5 text-sm">
                            {columns
                                .filter((column) => !column.hideOnMobile)
                                .map((column) => (
                                    <div key={column.key} className="contents">
                                        <dt className="text-fg-subtle">{column.header}</dt>
                                        <dd className="min-w-0 break-words text-fg">{column.cell(row)}</dd>
                                    </div>
                                ))}
                        </dl>
                    </li>
                ))}
            </ul>
        </>
    );
}

function SortButton<T>({ column, sort, onSort }: { column: Column<T>; sort?: string; onSort: (sort: string) => void }) {
    const key = column.sort as string;
    const active = sort === key || sort === `-${key}`;
    const descending = sort === `-${key}`;
    const Icon = !active ? ChevronsUpDown : descending ? ArrowDown : ArrowUp;
    return (
        <button
            type="button"
            onClick={() => onSort(active && !descending ? `-${key}` : key)}
            className={cn('inline-flex items-center gap-1 uppercase hover:text-brand-700', active && 'text-brand-700')}
            aria-label={`Ordina per ${column.header}`}
        >
            {column.header}
            <Icon className="size-3.5" aria-hidden />
        </button>
    );
}
