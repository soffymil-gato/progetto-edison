import { useForm } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import type { FormEvent } from 'react';

import { Badge } from '@/design-system/Badge';
import { Button } from '@/design-system/Button';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { DescriptionList } from '@/design-system/DescriptionList';
import { Field } from '@/design-system/Field';
import { PasswordInput } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { PasswordFields } from '@/features/PasswordFields';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';

type Props = {
    account: {
        name: string;
        email: string;
        username: string | null;
        roles: string[];
        lastLoginAt: string | null;
        passwordChangedAt: string | null;
    };
    passwordMinLength: number;
    urls: { password: string };
};

export default function Profile({ account, passwordMinLength, urls }: Props) {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(urls.password, { preserveScroll: true, onSuccess: () => form.reset(), onError: () => form.reset('current_password') });
    };

    return (
        <AppShell title="Profilo e sicurezza">
            <PageHeader title="Profilo e sicurezza" description="I tuoi dati di accesso e la gestione della password." />
            <div className="grid gap-6 lg:grid-cols-[1fr_1.1fr]">
                <Card>
                    <CardHeader title="Account" />
                    <CardBody className="py-2">
                        <DescriptionList
                            items={[
                                { term: 'Nome', value: account.name },
                                { term: 'Email', value: account.email },
                                ...(account.username ? [{ term: 'Nome utente', value: account.username }] : []),
                                ...(account.roles.length > 0
                                    ? [
                                          {
                                              term: 'Ruoli',
                                              value: (
                                                  <span className="flex flex-wrap gap-1.5">
                                                      {account.roles.map((role) => (
                                                          <Badge key={role} tone="brand">
                                                              {role}
                                                          </Badge>
                                                      ))}
                                                  </span>
                                              ),
                                          },
                                      ]
                                    : []),
                                { term: 'Ultimo accesso', value: formatDateTime(account.lastLoginAt) },
                                { term: 'Password modificata', value: formatDateTime(account.passwordChangedAt) },
                            ]}
                        />
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Cambia password" description="Alla conferma le altre sessioni aperte verranno chiuse." />
                    <CardBody>
                        <form onSubmit={submit} className="space-y-5" noValidate>
                            <Field label="Password attuale" error={form.errors.current_password} required>
                                {(field) => (
                                    <PasswordInput
                                        id={field.id}
                                        aria-describedby={field.describedBy}
                                        invalid={field.invalid}
                                        value={form.data.current_password}
                                        onChange={(e) => form.setData('current_password', e.target.value)}
                                        autoComplete="current-password"
                                        required
                                    />
                                )}
                            </Field>
                            <PasswordFields form={form} minLength={passwordMinLength} />
                            <Button type="submit" loading={form.processing} icon={<KeyRound className="size-4" aria-hidden />}>
                                Aggiorna la password
                            </Button>
                        </form>
                    </CardBody>
                </Card>
            </div>
        </AppShell>
    );
}
