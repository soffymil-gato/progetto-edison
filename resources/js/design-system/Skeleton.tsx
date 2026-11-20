import { cn } from '@/lib/cn';

export function Skeleton({ className }: { className?: string }) {
    return <div className={cn('skeleton h-4', className)} aria-hidden />;
}

/** Table rows placeholder shown while a filtered page is loading. */
export function TableSkeleton({ rows = 5, columns = 4 }: { rows?: number; columns?: number }) {
    return (
        <div className="divide-y divide-line" role="status" aria-label="Caricamento in corso">
            {Array.from({ length: rows }, (_, row) => (
                <div key={row} className="flex items-center gap-6 px-5 py-4">
                    {Array.from({ length: columns }, (_, col) => (
                        <Skeleton key={col} className={cn('h-3.5', col === 0 ? 'w-1/4' : 'w-1/6')} />
                    ))}
                </div>
            ))}
        </div>
    );
}
