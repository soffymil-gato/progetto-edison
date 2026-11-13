import type { ReactNode } from 'react';

export function DescriptionList({ items }: { items: { term: string; value: ReactNode }[] }) {
    return (
        <dl className="divide-y divide-line">
            {items.map((item) => (
                <div key={item.term} className="grid gap-1 py-3 sm:grid-cols-[12rem_1fr] sm:gap-4">
                    <dt className="text-sm text-fg-subtle">{item.term}</dt>
                    <dd className="min-w-0 text-sm break-words text-fg">{item.value}</dd>
                </div>
            ))}
        </dl>
    );
}
