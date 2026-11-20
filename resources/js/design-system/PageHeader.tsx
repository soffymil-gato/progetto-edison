import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';

export function PageHeader({
    title,
    description,
    actions,
    back,
    eyebrow,
}: {
    title: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    back?: { href: string; label: string };
    eyebrow?: ReactNode;
}) {
    return (
        <div className="mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
            <div className="min-w-0">
                {back && (
                    <Link
                        href={back.href}
                        className="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-fg-muted transition-colors hover:text-brand-700"
                    >
                        <ArrowLeft className="size-4" aria-hidden />
                        {back.label}
                    </Link>
                )}
                {eyebrow && (
                    <p className="mb-1.5 text-xs font-semibold tracking-[0.14em] text-brand-600 uppercase">{eyebrow}</p>
                )}
                <h1 className="text-2xl font-semibold text-fg sm:text-[1.75rem]">{title}</h1>
                {description && <p className="mt-1.5 max-w-2xl text-[0.9375rem] text-fg-muted">{description}</p>}
            </div>
            {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
