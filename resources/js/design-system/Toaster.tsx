import { CircleAlert, CircleCheck, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { cn } from '@/lib/cn';
import { useShared } from '@/lib/useShared';

type Toast = { id: number; tone: 'success' | 'error'; message: string };

/**
 * Shows the server flash messages (success / error) as accessible toasts.
 */
export function Toaster() {
    const { flash } = useShared();
    const [toasts, setToasts] = useState<Toast[]>([]);

    useEffect(() => {
        const next: Toast[] = [];
        if (flash.success) next.push({ id: Date.now(), tone: 'success', message: flash.success });
        if (flash.error) next.push({ id: Date.now() + 1, tone: 'error', message: flash.error });
        if (next.length === 0) return;
        // eslint-disable-next-line react-hooks/set-state-in-effect -- flash props arrive with each navigation
        setToasts((current) => [...current, ...next].slice(-3));
        const timers = next.map((toast) =>
            window.setTimeout(() => setToasts((c) => c.filter((t) => t.id !== toast.id)), 6000),
        );
        return () => timers.forEach((t) => window.clearTimeout(t));
    }, [flash]);

    return (
        <div
            aria-live="polite"
            className="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
        >
            {toasts.map((toast) => (
                <div
                    key={toast.id}
                    role={toast.tone === 'error' ? 'alert' : 'status'}
                    className={cn(
                        'pointer-events-auto flex w-full max-w-sm animate-fade-up items-start gap-3 rounded-card border bg-surface px-4 py-3.5 text-sm shadow-elevated',
                        toast.tone === 'success' ? 'border-success-600/25' : 'border-danger-600/30',
                    )}
                >
                    {toast.tone === 'success' ? (
                        <CircleCheck className="mt-px size-5 shrink-0 text-success-600" aria-hidden />
                    ) : (
                        <CircleAlert className="mt-px size-5 shrink-0 text-danger-600" aria-hidden />
                    )}
                    <p className="min-w-0 flex-1 leading-relaxed text-fg">{toast.message}</p>
                    <button
                        type="button"
                        onClick={() => setToasts((c) => c.filter((t) => t.id !== toast.id))}
                        className="-m-1 rounded-md p-1 text-fg-subtle hover:bg-canvas hover:text-fg"
                        aria-label="Chiudi notifica"
                    >
                        <X className="size-4" aria-hidden />
                    </button>
                </div>
            ))}
        </div>
    );
}
