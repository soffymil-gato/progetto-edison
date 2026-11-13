import type { ReactNode } from 'react';

export function EmptyState({ icon, title, description, action }: { icon: ReactNode; title: string; description?: ReactNode; action?: ReactNode }) {
    return (
        <div className="flex flex-col items-center px-6 py-14 text-center">
            <div className="mb-4 flex size-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">{icon}</div>
            <p className="font-display text-base font-semibold text-fg">{title}</p>
            {description && <p className="mt-1 max-w-md text-sm text-fg-muted">{description}</p>}
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}
