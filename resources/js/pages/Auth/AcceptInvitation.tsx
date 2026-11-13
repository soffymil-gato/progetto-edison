import { useForm } from '@inertiajs/react';
import { Clock, ShieldCheck } from 'lucide-react';
import type { FormEvent } from 'react';

import { Alert } from '@/design-system/Alert';
import { Button, ButtonLink } from '@/design-system/Button';
import { PasswordFields } from '@/features/PasswordFields';
import { AuthLayout } from '@/layouts/AuthLayout';

type Props = { valid: boolean; email: string | null; passwordMinLength: number; urls: { store: string; login: string } };

export default function AcceptInvitation({ valid, email, passwordMinLength, urls }: Props) {
    const form = useForm({ password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.store, { onFinish: () => form.reset() });
    };

    if (!valid) {
        return (
            <AuthLayout title="Invito non valido">
                <Alert tone="warning" title="Il link non è più utilizzabile">
                    L'invito potrebbe essere scaduto, già utilizzato o sostituito da uno più recente. Chiedi all'amministrazione di inviarne uno nuovo.
                </Alert>
                <ButtonLink href={urls.login} variant="secondary" className="mt-6 w-full">
                    Vai all'accesso
                </ButtonLink>
            </AuthLayout>
        );
    }

    return (
        <AuthLayout title="Attiva il tuo account" description="Scegli la password personale con cui accederai. Nessun altro la conoscerà.">
            <div className="mb-6 flex items-center gap-3 rounded-card border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-900">
                <ShieldCheck className="size-5 shrink-0 text-brand-600" aria-hidden />
                <span className="min-w-0 break-all">{email}</span>
            </div>
            <form onSubmit={submit} className="space-y-5" noValidate>
                <PasswordFields form={form} minLength={passwordMinLength} label="Password" />
                <Button type="submit" size="lg" className="w-full" loading={form.processing}>
                    Attiva l'account
                </Button>
            </form>
            <p className="mt-6 flex items-center gap-2 text-xs text-fg-subtle">
                <Clock className="size-3.5" aria-hidden />
                Il link di invito è personale e ha una scadenza.
            </p>
        </AuthLayout>
    );
}
