import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Send } from 'lucide-react';
import type { FormEvent } from 'react';

import { Alert } from '@/design-system/Alert';
import { Button } from '@/design-system/Button';
import { Field } from '@/design-system/Field';
import { Input } from '@/design-system/Input';
import { AuthLayout } from '@/layouts/AuthLayout';

type Props = { urls: { store: string; login: string } };

export default function ForgotPassword({ urls }: Props) {
    const form = useForm({ email: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.store, { preserveScroll: true });
    };

    return (
        <AuthLayout title="Recupera la password" description="Indica l'email del tuo account: ti invieremo un link per scegliere una nuova password.">
            {form.wasSuccessful && (
                <Alert tone="success" className="mb-6">
                    Se l'indirizzo corrisponde a un account attivo, riceverai a breve un'email con le istruzioni.
                </Alert>
            )}
            <form onSubmit={submit} className="space-y-5" noValidate>
                <Field label="Email" error={form.errors.email} required>
                    {(field) => (
                        <Input
                            id={field.id}
                            type="email"
                            aria-describedby={field.describedBy}
                            invalid={field.invalid}
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            autoComplete="email"
                            autoFocus
                            required
                        />
                    )}
                </Field>
                <Button type="submit" size="lg" className="w-full" loading={form.processing} icon={<Send className="size-4" aria-hidden />}>
                    Invia il link
                </Button>
            </form>
            <Link href={urls.login} className="mt-8 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:underline">
                <ArrowLeft className="size-4" aria-hidden />
                Torna all'accesso
            </Link>
        </AuthLayout>
    );
}
