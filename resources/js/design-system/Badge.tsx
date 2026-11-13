import type { ReactNode } from 'react';

import { cn } from '@/lib/cn';

export type Tone = 'neutral' | 'brand' | 'success' | 'warning' | 'danger';

const tones: Record<Tone, string> = {
    neutral: 'bg-slate-100 text-slate-700 ring-slate-200',
    brand: 'bg-brand-50 text-brand-800 ring-brand-200',
    success: 'bg-success-50 text-success-700 ring-success-600/20',
    warning: 'bg-warning-50 text-warning-700 ring-warning-600/25',
    danger: 'bg-danger-50 text-danger-700 ring-danger-600/20',
};

export function Badge({
    tone = 'neutral',
    dot = false,
    children,
    className,
}: {
    tone?: Tone;
    dot?: boolean;
    children: ReactNode;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset',
                tones[tone],
                className,
            )}
        >
            {dot && <span className="size-1.5 rounded-full bg-current" aria-hidden />}
            {children}
        </span>
    );
}

/** Status of accounts: colour is always paired with a text label. */
export function AccountStatusBadge({ status, label }: { status: string; label: string }) {
    const tone: Tone = status === 'active' ? 'success' : status === 'suspended' ? 'danger' : 'warning';
    return (
        <Badge tone={tone} dot>
            {label}
        </Badge>
    );
}
