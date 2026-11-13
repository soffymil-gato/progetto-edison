import { Eye, EyeOff } from 'lucide-react';
import { type ComponentProps, useState } from 'react';

import { cn } from '@/lib/cn';

import { controlClasses, controlState } from './Field';

type ControlProps = { invalid?: boolean };

export function Input({ invalid = false, className, ...props }: ControlProps & ComponentProps<'input'>) {
    return <input className={cn(controlClasses, controlState(invalid), 'h-11', className)} aria-invalid={invalid || undefined} {...props} />;
}

export function Textarea({ invalid = false, className, rows = 4, ...props }: ControlProps & ComponentProps<'textarea'>) {
    return (
        <textarea
            rows={rows}
            className={cn(controlClasses, controlState(invalid), 'min-h-24 py-2.5 leading-relaxed', className)}
            aria-invalid={invalid || undefined}
            {...props}
        />
    );
}

export function Select({ invalid = false, className, children, ...props }: ControlProps & ComponentProps<'select'>) {
    return (
        <select
            className={cn(
                controlClasses,
                controlState(invalid),
                'h-11 appearance-none bg-[url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%20viewBox%3D%270%200%2020%2020%27%20fill%3D%27%236b7891%27%3E%3Cpath%20d%3D%27M5.5%207.5l4.5%204.5%204.5-4.5%27%20stroke%3D%27%236b7891%27%20stroke-width%3D%271.6%27%20fill%3D%27none%27%20stroke-linecap%3D%27round%27/%3E%3C/svg%3E")] bg-[length:1.1rem] bg-[right_0.75rem_center] bg-no-repeat pr-10',
                className,
            )}
            aria-invalid={invalid || undefined}
            {...props}
        >
            {children}
        </select>
    );
}

/** Password input with show/hide toggle (accessible, keyboard operable). */
export function PasswordInput({ invalid = false, className, ...props }: ControlProps & Omit<ComponentProps<'input'>, 'type'>) {
    const [visible, setVisible] = useState(false);
    return (
        <div className="relative">
            <input
                type={visible ? 'text' : 'password'}
                className={cn(controlClasses, controlState(invalid), 'h-11 pr-12', className)}
                aria-invalid={invalid || undefined}
                autoCapitalize="none"
                spellCheck={false}
                {...props}
            />
            <button
                type="button"
                onClick={() => setVisible((v) => !v)}
                className="absolute inset-y-1 right-1 inline-flex w-10 items-center justify-center rounded-[0.5rem] text-fg-subtle transition-colors hover:bg-brand-50 hover:text-brand-700"
                aria-label={visible ? 'Nascondi password' : 'Mostra password'}
                aria-pressed={visible}
            >
                {visible ? <EyeOff className="size-4.5" aria-hidden /> : <Eye className="size-4.5" aria-hidden />}
            </button>
        </div>
    );
}

export function Checkbox({ className, ...props }: Omit<ComponentProps<'input'>, 'type'>) {
    return (
        <input
            type="checkbox"
            className={cn(
                'size-4.5 shrink-0 cursor-pointer rounded-[0.3rem] border-line-strong accent-brand-600 focus-visible:outline-2',
                className,
            )}
            {...props}
        />
    );
}
