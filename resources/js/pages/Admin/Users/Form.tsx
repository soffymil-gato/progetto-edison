import { router, useForm } from '@inertiajs/react';
import { Ban, KeyRound, MailPlus, RotateCcw, Save } from 'lucide-react';
import type { FormEvent } from 'react';

import { Alert } from '@/design-system/Alert';
import { AccountStatusBadge } from '@/design-system/Badge';
import { Button } from '@/design-system/Button';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { ConfirmDialog } from '@/design-system/ConfirmDialog';
import { DescriptionList } from '@/design-system/DescriptionList';
import { Field } from '@/design-system/Field';
import { Checkbox, Input } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';
import type { Option } from '@/lib/types';
import { useShared } from '@/lib/useShared';

type User = {
    id: number;
    name: string;
    email: string;
    status: string;
    statusLabel: string;
    roles: string[];
    lastLoginAt: string | null;
    createdAt: string | null;
    isSelf: boolean;
    canModify: boolean;
    hasPassword: boolean;
};

type Props = {
    user: User | null;
    assignableRoles: Option[];
    urls: { submit: string; back: string; suspend?: string; reactivate?: string; invitation?: string; passwordReset?: string };
};

export default function UserForm({ user, assignableRoles, urls }: Props) {
    const { errors } = useShared();
    const form = useForm({ name: user?.name ?? '', email: user?.email ?? '', roles: user?.roles ?? [] });
    const editing = user !== null;
    const readOnly = editing && !user.canModify;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) form.put(urls.submit, { preserveScroll: true });
        else form.post(urls.submit);
    };

    const toggleRole = (role: string, checked: boolean) =>
        form.setData('roles', checked ? [...form.data.roles, role] : form.data.roles.filter((r) => r !== role));

    const post = (url: string | undefined) => url && router.post(url, {}, { preserveScroll: true });

    return (
        <AppShell title={editing ? user.name : 'Nuovo utente'}>
            <PageHeader
                back={{ href: urls.back, label: 'Utenti interni' }}
                title={editing ? user.name : 'Nuovo utente'}
                description={editing ? user.email : "L'utente riceverà un invito via email per scegliere la propria password."}
                actions={editing && <AccountStatusBadge status={user.status} label={user.statusLabel} />}
            />

            {errors.user && <Alert tone="danger" className="mb-6">{errors.user}</Alert>}
            {readOnly && (
                <Alert tone="warning" className="mb-6">
                    Solo un Super Admin può modificare questo utente.
                </Alert>
            )}

            <div className="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <Card>
                    <CardHeader title="Dati e ruoli" />
                    <CardBody>
                        <form onSubmit={submit} className="space-y-6" noValidate>
                            <div className="grid gap-5 sm:grid-cols-2">
                                <Field label="Nome e cognome" error={form.errors.name} required>
                                    {(f) => (
                                        <Input id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} disabled={readOnly} required />
                                    )}
                                </Field>
                                <Field label="Email aziendale" error={form.errors.email} hint={editing ? "L'email non è modificabile." : undefined} required>
                                    {(f) => (
                                        <Input
                                            id={f.id}
                                            type="email"
                                            aria-describedby={f.describedBy}
                                            invalid={f.invalid}
                                            value={form.data.email}
                                            onChange={(e) => form.setData('email', e.target.value)}
                                            disabled={editing}
                                            autoComplete="off"
                                            required
                                        />
                                    )}
                                </Field>
                            </div>

                            <fieldset>
                                <legend className="text-sm font-medium text-fg">
                                    Ruoli <span className="text-danger-600" aria-hidden>*</span>
                                </legend>
                                <p className="mt-0.5 text-xs text-fg-subtle">
                                    {user?.isSelf ? 'Non puoi modificare i tuoi ruoli.' : 'Sono elencati solo i ruoli che hai i privilegi di assegnare.'}
                                </p>
                                <div className="mt-3 grid gap-2 sm:grid-cols-2">
                                    {assignableRoles.map((role) => (
                                        <label key={role.value} className="flex cursor-pointer items-center gap-3 rounded-xl border border-line px-3.5 py-3 transition-colors has-[:checked]:border-brand-300 has-[:checked]:bg-brand-50 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
                                            <Checkbox
                                                checked={form.data.roles.includes(role.value)}
                                                onChange={(e) => toggleRole(role.value, e.target.checked)}
                                                disabled={readOnly || user?.isSelf}
                                            />
                                            <span className="text-sm font-medium text-fg">{role.label}</span>
                                        </label>
                                    ))}
                                </div>
                                {(form.errors.roles || errors.roles) && (
                                    <p className="mt-2 text-sm text-danger-700" role="alert">
                                        {form.errors.roles ?? errors.roles}
                                    </p>
                                )}
                            </fieldset>

                            {!readOnly && (
                                <div className="flex justify-end border-t border-line pt-5">
                                    <Button type="submit" loading={form.processing} icon={<Save className="size-4" aria-hidden />}>
                                        {editing ? 'Salva le modifiche' : 'Crea e invia invito'}
                                    </Button>
                                </div>
                            )}
                        </form>
                    </CardBody>
                </Card>

                {editing && (
                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader title="Accesso" />
                            <CardBody className="py-2">
                                <DescriptionList
                                    items={[
                                        { term: 'Creato il', value: formatDateTime(user.createdAt) },
                                        { term: 'Ultimo accesso', value: formatDateTime(user.lastLoginAt) },
                                    ]}
                                />
                            </CardBody>
                        </Card>

                        {user.canModify && !user.isSelf && (
                            <Card>
                                <CardHeader title="Azioni sull'account" />
                                <CardBody className="flex flex-col gap-3">
                                    {user.status === 'invited' && (
                                        <Button variant="secondary" onClick={() => post(urls.invitation)} icon={<MailPlus className="size-4" aria-hidden />}>
                                            Invia un nuovo invito
                                        </Button>
                                    )}
                                    {user.status === 'active' && (
                                        <Button variant="secondary" onClick={() => post(urls.passwordReset)} icon={<KeyRound className="size-4" aria-hidden />}>
                                            Invia reset della password
                                        </Button>
                                    )}
                                    {user.status === 'suspended' ? (
                                        <Button variant="secondary" onClick={() => post(urls.reactivate)} icon={<RotateCcw className="size-4" aria-hidden />}>
                                            Riattiva l'utente
                                        </Button>
                                    ) : (
                                        <ConfirmDialog
                                            trigger={
                                                <Button variant="secondary" className="text-danger-700 hover:border-danger-600/40 hover:bg-danger-50" icon={<Ban className="size-4" aria-hidden />}>
                                                    Sospendi l'utente
                                                </Button>
                                            }
                                            title={`Sospendere ${user.name}?`}
                                            description="L'utente non potrà più accedere e le sue sessioni aperte verranno chiuse subito. Potrai riattivarlo in seguito."
                                            confirmLabel="Sospendi"
                                            onConfirm={() => post(urls.suspend)}
                                        />
                                    )}
                                </CardBody>
                            </Card>
                        )}
                    </div>
                )}
            </div>
        </AppShell>
    );
}
