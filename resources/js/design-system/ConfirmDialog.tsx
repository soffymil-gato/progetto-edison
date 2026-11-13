import { AlertDialog } from 'radix-ui';
import type { ReactNode } from 'react';

import { Button, buttonClasses } from './Button';

/**
 * Confirmation for consequential actions (suspend a user, delete a role…).
 */
export function ConfirmDialog({
    trigger,
    title,
    description,
    confirmLabel,
    tone = 'danger',
    onConfirm,
    processing = false,
}: {
    trigger: ReactNode;
    title: string;
    description: ReactNode;
    confirmLabel: string;
    tone?: 'danger' | 'primary';
    onConfirm: () => void;
    processing?: boolean;
}) {
    return (
        <AlertDialog.Root>
            <AlertDialog.Trigger asChild>{trigger}</AlertDialog.Trigger>
            <AlertDialog.Portal>
                <AlertDialog.Overlay className="fixed inset-0 z-50 animate-fade-in bg-ink/40 backdrop-blur-[2px]" />
                <AlertDialog.Content className="fixed top-1/2 left-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 animate-fade-in rounded-panel border border-line bg-surface p-6 shadow-elevated">
                    <AlertDialog.Title className="font-display text-lg font-semibold text-fg">
                        {title}
                    </AlertDialog.Title>
                    <AlertDialog.Description className="mt-2 text-sm leading-relaxed text-fg-muted">
                        {description}
                    </AlertDialog.Description>
                    <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <AlertDialog.Cancel className={buttonClasses({ variant: 'secondary' })}>
                            Annulla
                        </AlertDialog.Cancel>
                        <AlertDialog.Action asChild>
                            <Button variant={tone} loading={processing} onClick={onConfirm}>
                                {confirmLabel}
                            </Button>
                        </AlertDialog.Action>
                    </div>
                </AlertDialog.Content>
            </AlertDialog.Portal>
        </AlertDialog.Root>
    );
}
