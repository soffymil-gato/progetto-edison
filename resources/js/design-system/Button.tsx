import { Link } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import type { ComponentProps, ReactNode } from 'react';

import { cn } from '@/lib/cn';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger' | 'inverse';
type Size = 'sm' | 'md' | 'lg';

const base =
    'group/button relative inline-flex select-none items-center justify-center gap-2 whitespace-nowrap rounded-control font-medium transition-[background-color,box-shadow,color,transform] duration-200 ease-[var(--ease-soft)] focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-55 active:translate-y-px';

const variants: Record<Variant, string> = {
    primary:
        'bg-brand-600 text-white shadow-[0_1px_0_rgb(255_255_255/0.15)_inset,0_6px_16px_-6px_rgb(22_86_224/0.6)] hover:bg-brand-700',
    secondary: 'border border-line-strong bg-surface text-fg shadow-xs hover:border-brand-300 hover:bg-brand-50',
    ghost: 'text-fg-muted hover:bg-brand-50 hover:text-brand-800',
    danger: 'bg-danger-600 text-white hover:bg-danger-700',
    inverse: 'bg-white text-brand-900 shadow-[0_10px_30px_-10px_rgb(0_0_0/0.5)] hover:bg-brand-50',
};

const sizes: Record<Size, string> = {
    sm: 'h-9 px-3 text-sm',
    md: 'h-11 px-4.5 text-sm',
    lg: 'h-13 px-6 text-base',
};

type CommonProps = { variant?: Variant; size?: Size; loading?: boolean; icon?: ReactNode; className?: string };

export function buttonClasses({ variant = 'primary', size = 'md', className }: CommonProps = {}): string {
    return cn(base, variants[variant], sizes[size], className);
}

export function Button({
    variant,
    size,
    loading = false,
    icon,
    className,
    children,
    disabled,
    type = 'button',
    ...props
}: CommonProps & ComponentProps<'button'>) {
    return (
        <button
            type={type}
            className={buttonClasses({ variant, size, className })}
            disabled={disabled || loading}
            aria-busy={loading || undefined}
            {...props}
        >
            {loading ? <LoaderCircle className="size-4 animate-spin" aria-hidden /> : icon}
            {children}
        </button>
    );
}

/** Navigation styled as a button (Inertia link, or a plain anchor for external/full-page URLs). */
export function ButtonLink({
    variant,
    size,
    icon,
    className,
    children,
    href,
    external = false,
}: CommonProps & { href: string; children: ReactNode; external?: boolean }) {
    const classes = buttonClasses({ variant, size, className });
    if (external) {
        return (
            <a href={href} className={classes}>
                {icon}
                {children}
            </a>
        );
    }
    return (
        <Link href={href} className={classes}>
            {icon}
            {children}
        </Link>
    );
}
