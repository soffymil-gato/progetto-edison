import { CircleAlert, CircleCheck, Info, TriangleAlert } from 'lucide-react';
import type { ReactNode } from 'react';

import { cn } from '@/lib/cn';

type Tone = 'info' | 'success' | 'warning' | 'danger';

const styles: Record<Tone, { box: string; icon: ReactNode }> = {
    info: {
        box: 'border-brand-200 bg-brand-50 text-brand-900',
        icon: <Info className="size-5 text-brand-600" aria-hidden />,
    },
    success: {
        box: 'border-success-600/25 bg-success-50 text-success-700',
        icon: <CircleCheck className="size-5" aria-hidden />,
    },
    warning: {
        box: 'border-warning-600/30 bg-warning-50 text-warning-700',
        icon: <TriangleAlert className="size-5" aria-hidden />,
    },
    danger: {
        box: 'border-danger-600/25 bg-danger-50 text-danger-700',
        icon: <CircleAlert className="size-5" aria-hidden />,
    },
};

export function Alert({
    tone = 'info',
    title,
    children,
    className,
}: {
    tone?: Tone;
    title?: ReactNode;
    children?: ReactNode;
    className?: string;
}) {
    const style = styles[tone];
    return (
        <div
            role={tone === 'danger' ? 'alert' : 'status'}
            className={cn('flex gap-3 rounded-card border px-4 py-3.5 text-sm', style.box, className)}
        >
            <span className="mt-px shrink-0">{style.icon}</span>
            <div className="min-w-0 leading-relaxed">
                {title && <p className="font-semibold">{title}</p>}
                {children && <div className={cn(title && 'mt-0.5 opacity-90')}>{children}</div>}
            </div>
        </div>
    );
}
