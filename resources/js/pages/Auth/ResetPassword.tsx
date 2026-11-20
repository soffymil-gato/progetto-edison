import { useForm } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import type { FormEvent } from 'react';

import { Button } from '@/design-system/Button';
import { Field } from '@/design-system/Field';
import { Input } from '@/design-system/Input';
import { PasswordFields } from '@/features/PasswordFields';
import { AuthLayout } from '@/layouts/AuthLayout';

type Props = { token: string; email: string; passwordMinLength: number; urls: { store: string; login: string } };

export default function ResetPassword({ token, email, passwordMinLength, urls }: Props) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.store, { onFinish: () => form.reset('password', 'password_confirmation') });
    };

    return (
        <AuthLayout
            title="Imposta una nuova password"
            description="Scegli la nuova password del tuo account. Le sessioni aperte su altri dispositivi verranno chiuse."
        >
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
                            required
                        />
                    )}
                </Field>
                <PasswordFields form={form} minLength={passwordMinLength} />
                <Button
                    type="submit"
                    size="lg"
                    className="w-full"
                    loading={form.processing}
                    icon={<KeyRound className="size-4" aria-hidden />}
                >
                    Salva la nuova password
                </Button>
            </form>
        </AuthLayout>
    );
}
