import type { ComponentProps, ReactNode } from 'react';

import { cn } from '@/lib/cn';

export function Card({ className, children, ...props }: ComponentProps<'section'>) {
    return (
        <section className={cn('rounded-card border border-line bg-surface shadow-card', className)} {...props}>
            {children}
        </section>
    );
}

export function CardHeader({
    title,
    description,
    actions,
    className,
}: {
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    className?: string;
}) {
    return (
        <header
            className={cn(
                'flex flex-wrap items-start justify-between gap-3 border-b border-line px-5 py-4 sm:px-6',
                className,
            )}
        >
            <div className="min-w-0">
                <h2 className="text-base font-semibold text-fg">{title}</h2>
                {description && <p className="mt-0.5 text-sm text-fg-muted">{description}</p>}
            </div>
            {actions && <div className="flex shrink-0 items-center gap-2">{actions}</div>}
        </header>
    );
}

export function CardBody({ className, children }: { className?: string; children: ReactNode }) {
    return <div className={cn('px-5 py-5 sm:px-6', className)}>{children}</div>;
}
