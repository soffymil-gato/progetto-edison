import { Link, useForm } from '@inertiajs/react';
import { LogIn } from 'lucide-react';
import type { FormEvent } from 'react';

import { Button } from '@/design-system/Button';
import { Field } from '@/design-system/Field';
import { Input, PasswordInput } from '@/design-system/Input';
import { AuthLayout } from '@/layouts/AuthLayout';
import { useShared } from '@/lib/useShared';

type Props = { identifierLabel: string; urls: { store: string; forgot: string } };

export default function Login({ identifierLabel, urls }: Props) {
    const { area } = useShared();
    const form = useForm({ identifier: '', password: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.store, { onFinish: () => form.reset('password') });
    };

    return (
        <AuthLayout
            title="Accedi"
            description={
                area === 'admin'
                    ? 'Inserisci le credenziali del tuo account aziendale.'
                    : "L'accesso è riservato ai clienti autorizzati dall'amministrazione."
            }
        >
            <form onSubmit={submit} className="space-y-5" noValidate>
                <Field label={identifierLabel} error={form.errors.identifier} required>
                    {(field) => (
                        <Input
                            id={field.id}
                            aria-describedby={field.describedBy}
                            invalid={field.invalid}
                            value={form.data.identifier}
                            onChange={(e) => form.setData('identifier', e.target.value)}
                            autoComplete="username"
                            autoCapitalize="none"
                            spellCheck={false}
                            autoFocus
                            required
                        />
                    )}
                </Field>
                <Field label="Password" error={form.errors.password} required>
                    {(field) => (
                        <PasswordInput
                            id={field.id}
                            aria-describedby={field.describedBy}
                            invalid={field.invalid}
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    )}
                </Field>
                <div className="flex justify-end">
                    <Link href={urls.forgot} className="text-sm font-medium text-brand-700 hover:text-brand-900 hover:underline">
                        Password dimenticata?
                    </Link>
                </div>
                <Button type="submit" size="lg" className="w-full" loading={form.processing} icon={<LogIn className="size-4" aria-hidden />}>
                    Accedi
                </Button>
            </form>
            <p className="mt-8 text-center text-xs leading-relaxed text-fg-subtle">
                {area === 'admin'
                    ? 'Accesso riservato al personale autorizzato. Le attività sono registrate.'
                    : "Non hai le credenziali? Contatta l'amministrazione: gli account sono creati solo su autorizzazione."}
            </p>
        </AuthLayout>
    );
}
