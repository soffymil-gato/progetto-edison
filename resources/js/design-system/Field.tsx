import { type ReactNode, useId } from 'react';

import { cn } from '@/lib/cn';

type FieldRenderProps = { id: string; describedBy: string | undefined; invalid: boolean };

/**
 * Accessible form field: label, hint and error are wired with aria attributes.
 */
export function Field({
    label,
    hint,
    error,
    required = false,
    className,
    children,
}: {
    label: ReactNode;
    hint?: ReactNode;
    error?: string | undefined;
    required?: boolean;
    className?: string;
    children: (props: FieldRenderProps) => ReactNode;
}) {
    const id = useId();
    const hintId = hint ? `${id}-hint` : undefined;
    const errorId = error ? `${id}-error` : undefined;
    const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

    return (
        <div className={cn('flex flex-col gap-1.5', className)}>
            <label htmlFor={id} className="text-sm font-medium text-fg">
                {label}
                {required && (
                    <span className="ml-0.5 text-danger-600" aria-hidden>
                        *
                    </span>
                )}
            </label>
            {children({ id, describedBy, invalid: Boolean(error) })}
            {hint && !error && (
                <p id={hintId} className="text-xs leading-relaxed text-fg-subtle">
                    {hint}
                </p>
            )}
            {error && (
                <p id={errorId} className="text-sm text-danger-700" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

export const controlClasses =
    'w-full rounded-control border bg-surface px-3.5 text-[0.9375rem] text-fg shadow-xs transition-[border-color,box-shadow] duration-150 placeholder:text-fg-subtle/80 focus:border-brand-500 focus:shadow-[0_0_0_4px_rgb(42_111_255/0.14)] focus:outline-none disabled:cursor-not-allowed disabled:bg-canvas disabled:text-fg-subtle';

export function controlState(invalid: boolean): string {
    return invalid
        ? 'border-danger-600 focus:border-danger-600 focus:shadow-[0_0_0_4px_rgb(198_47_61/0.12)]'
        : 'border-line-strong';
}
