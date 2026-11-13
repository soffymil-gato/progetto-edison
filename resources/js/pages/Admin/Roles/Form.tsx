import { router, useForm } from '@inertiajs/react';
import { Lock, Save, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';

import { Alert } from '@/design-system/Alert';
import { Badge } from '@/design-system/Badge';
import { Button } from '@/design-system/Button';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { ConfirmDialog } from '@/design-system/ConfirmDialog';
import { Field } from '@/design-system/Field';
import { Checkbox, Input } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';
import { useShared } from '@/lib/useShared';

type PermissionOption = { value: string; label: string; sensitive: boolean; grantable: boolean };
type Role = { id: number; name: string; label: string; isSuperAdmin: boolean; isDefault: boolean; permissions: string[]; usersCount: number };

type Props = {
    role: Role | null;
    permissionGroups: { group: string; permissions: PermissionOption[] }[];
    urls: { submit: string; back: string; destroy?: string };
};

export default function RoleForm({ role, permissionGroups, urls }: Props) {
    const { errors } = useShared();
    const form = useForm({ name: role?.name ?? '', permissions: role?.permissions ?? [] });
    const editing = role !== null;
    const locked = role?.isSuperAdmin ?? false;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (editing) form.put(urls.submit, { preserveScroll: true });
        else form.post(urls.submit);
    };

    const toggle = (permission: string, checked: boolean) =>
        form.setData('permissions', checked ? [...form.data.permissions, permission] : form.data.permissions.filter((p) => p !== permission));

    return (
        <AppShell title={editing ? role.label : 'Nuovo ruolo'}>
            <PageHeader
                back={{ href: urls.back, label: 'Ruoli e permessi' }}
                title={editing ? role.label : 'Nuovo ruolo'}
                description={editing ? `${role.usersCount} ${role.usersCount === 1 ? 'utente assegnato' : 'utenti assegnati'}` : 'Definisci il nome e i permessi del ruolo.'}
            />

            {errors.role && <Alert tone="danger" className="mb-6">{errors.role}</Alert>}
            {locked && (
                <Alert tone="info" title="Ruolo protetto" className="mb-6">
                    Il Super Admin dispone implicitamente di tutti i permessi e non è modificabile.
                </Alert>
            )}

            <form onSubmit={submit} noValidate>
                <Card>
                    {!editing && (
                        <CardBody className="border-b border-line">
                            <Field label="Nome del ruolo" error={form.errors.name} required className="max-w-md">
                                {(f) => <Input id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />}
                            </Field>
                        </CardBody>
                    )}
                    <CardHeader title="Permessi" description="I permessi contrassegnati come sensibili possono essere concessi o revocati solo da un Super Admin." />
                    <CardBody className="space-y-6">
                        {permissionGroups.map((group) => (
                            <fieldset key={group.group}>
                                <legend className="text-xs font-semibold tracking-[0.12em] text-fg-subtle uppercase">{group.group}</legend>
                                <div className="mt-3 grid gap-2 md:grid-cols-2">
                                    {group.permissions.map((permission) => {
                                        const checked = locked || form.data.permissions.includes(permission.value);
                                        return (
                                            <label
                                                key={permission.value}
                                                className="flex cursor-pointer items-start gap-3 rounded-xl border border-line px-3.5 py-3 transition-colors has-[:checked]:border-brand-300 has-[:checked]:bg-brand-50 has-[:disabled]:cursor-not-allowed"
                                            >
                                                <Checkbox className="mt-0.5" checked={checked} disabled={locked || !permission.grantable} onChange={(e) => toggle(permission.value, e.target.checked)} />
                                                <span className="min-w-0 text-sm">
                                                    <span className="font-medium text-fg">{permission.label}</span>
                                                    {permission.sensitive && (
                                                        <Badge tone="warning" className="ml-2 align-middle">
                                                            <Lock className="size-3" aria-hidden /> Sensibile
                                                        </Badge>
                                                    )}
                                                    <span className="mt-0.5 block font-mono text-xs text-fg-subtle">{permission.value}</span>
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </fieldset>
                        ))}
                        {form.errors.permissions && <p className="text-sm text-danger-700" role="alert">{form.errors.permissions}</p>}
                    </CardBody>
                    {!locked && (
                        <div className="flex flex-col-reverse gap-3 border-t border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            {editing && !role.isDefault && urls.destroy ? (
                                <ConfirmDialog
                                    trigger={<Button variant="ghost" className="text-danger-700 hover:bg-danger-50 hover:text-danger-700" icon={<Trash2 className="size-4" aria-hidden />}>Elimina ruolo</Button>}
                                    title={`Eliminare il ruolo «${role.label}»?`}
                                    description="L'operazione è consentita solo se il ruolo non è assegnato a nessun utente."
                                    confirmLabel="Elimina"
                                    onConfirm={() => urls.destroy && router.delete(urls.destroy)}
                                />
                            ) : (
                                <span />
                            )}
                            <Button type="submit" loading={form.processing} icon={<Save className="size-4" aria-hidden />}>
                                {editing ? 'Salva i permessi' : 'Crea il ruolo'}
                            </Button>
                        </div>
                    )}
                </Card>
            </form>
        </AppShell>
    );
}
