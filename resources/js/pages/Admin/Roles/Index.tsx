import { Link } from '@inertiajs/react';
import { Lock, Plus, ShieldCheck } from 'lucide-react';

import { Badge } from '@/design-system/Badge';
import { ButtonLink } from '@/design-system/Button';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';

type Role = { id: number; name: string; label: string; isSuperAdmin: boolean; isDefault: boolean; permissionsCount: number; usersCount: number; editUrl: string };

export default function RolesIndex({ roles, urls }: { roles: Role[]; urls: { create: string } }) {
    return (
        <AppShell title="Ruoli e permessi">
            <PageHeader
                title="Ruoli e permessi"
                description="Ogni ruolo raccoglie permessi granulari, verificati dal server per ogni operazione."
                actions={<ButtonLink href={urls.create} icon={<Plus className="size-4" aria-hidden />}>Nuovo ruolo</ButtonLink>}
            />
            <ul className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {roles.map((role) => (
                    <li key={role.id}>
                        <Link href={role.editUrl} className="group flex h-full flex-col rounded-card border border-line bg-surface p-5 shadow-card transition-[border-color,transform,box-shadow] hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-elevated">
                            <div className="flex items-start justify-between gap-3">
                                <span className={role.isSuperAdmin ? 'inline-flex size-10 items-center justify-center rounded-xl bg-brand-950 text-spark-300' : 'inline-flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100'}>
                                    {role.isSuperAdmin ? <Lock className="size-5" aria-hidden /> : <ShieldCheck className="size-5" aria-hidden />}
                                </span>
                                {role.isDefault && <Badge>Predefinito</Badge>}
                            </div>
                            <p className="mt-4 font-display text-lg font-semibold text-fg">{role.label}</p>
                            <p className="mt-1 text-sm text-fg-muted">
                                {role.isSuperAdmin ? 'Tutti i permessi, incluse le configurazioni.' : `${role.permissionsCount} ${role.permissionsCount === 1 ? 'permesso' : 'permessi'}`}
                            </p>
                            <p className="mt-auto pt-4 text-xs text-fg-subtle">
                                {role.usersCount} {role.usersCount === 1 ? 'utente assegnato' : 'utenti assegnati'}
                            </p>
                        </Link>
                    </li>
                ))}
            </ul>
        </AppShell>
    );
}
