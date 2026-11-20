import { UserPlus, Users } from 'lucide-react';

import { AccountStatusBadge, Badge } from '@/design-system/Badge';
import { ButtonLink } from '@/design-system/Button';
import { Card } from '@/design-system/Card';
import { type Column, DataTable } from '@/design-system/DataTable';
import { EmptyState } from '@/design-system/EmptyState';
import { Select } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { Pagination } from '@/design-system/Pagination';
import { FilterBar } from '@/features/admin/FilterBar';
import { AppShell } from '@/layouts/AppShell';
import { formatRelative } from '@/lib/format';
import type { Option, Paginated } from '@/lib/types';
import { useFilters } from '@/lib/useFilters';

type UserRow = {
    id: number;
    name: string;
    email: string;
    status: string;
    statusLabel: string;
    roleLabels: string[];
    lastLoginAt: string | null;
    editUrl: string;
};

type Props = {
    users: Paginated<UserRow>;
    filters: { q: string; status: string; role: string; sort: string };
    roles: Option[];
    statuses: Option[];
    can: { manage: boolean };
    urls: { index: string; create: string };
};

export default function UsersIndex({ users, filters, roles, statuses, can, urls }: Props) {
    const { values, set, reset, active } = useFilters(urls.index, filters);

    const columns: Column<UserRow>[] = [
        {
            key: 'name',
            header: 'Utente',
            sort: 'name',
            cell: (u) => (
                <div className="min-w-0">
                    <p className="truncate font-medium text-fg">{u.name}</p>
                    <p className="truncate text-xs text-fg-muted">{u.email}</p>
                </div>
            ),
        },
        {
            key: 'roles',
            header: 'Ruoli',
            cell: (u) => (
                <span className="flex flex-wrap gap-1">
                    {u.roleLabels.length === 0 ? (
                        <span className="text-fg-subtle">—</span>
                    ) : (
                        u.roleLabels.map((r) => (
                            <Badge key={r} tone="brand">
                                {r}
                            </Badge>
                        ))
                    )}
                </span>
            ),
        },
        { key: 'status', header: 'Stato', cell: (u) => <AccountStatusBadge status={u.status} label={u.statusLabel} /> },
        {
            key: 'last',
            header: 'Ultimo accesso',
            sort: 'last_login_at',
            cell: (u) => <span className="text-fg-muted">{formatRelative(u.lastLoginAt)}</span>,
        },
    ];

    return (
        <AppShell title="Utenti interni">
            <PageHeader
                title="Utenti interni"
                description="Personale con accesso al gestionale. Gli utenti scelgono la propria password tramite invito."
                actions={
                    can.manage && (
                        <ButtonLink href={urls.create} icon={<UserPlus className="size-4" aria-hidden />}>
                            Nuovo utente
                        </ButtonLink>
                    )
                }
            />
            <Card>
                <FilterBar
                    search={values.q}
                    onSearch={(v) => set('q', v, true)}
                    searchLabel="Cerca per nome o email"
                    active={active}
                    onReset={reset}
                >
                    <Select
                        value={values.role}
                        onChange={(e) => set('role', e.target.value)}
                        aria-label="Filtra per ruolo"
                        className="lg:w-48"
                    >
                        <option value="">Tutti i ruoli</option>
                        {roles.map((r) => (
                            <option key={r.value} value={r.value}>
                                {r.label}
                            </option>
                        ))}
                    </Select>
                    <Select
                        value={values.status}
                        onChange={(e) => set('status', e.target.value)}
                        aria-label="Filtra per stato"
                        className="lg:w-44"
                    >
                        <option value="">Tutti gli stati</option>
                        {statuses.map((s) => (
                            <option key={s.value} value={s.value}>
                                {s.label}
                            </option>
                        ))}
                    </Select>
                </FilterBar>
                <DataTable
                    caption="Elenco degli utenti interni"
                    columns={columns}
                    rows={users.data}
                    rowKey={(u) => u.id}
                    sort={values.sort}
                    onSort={(s) => set('sort', s)}
                    rowHref={can.manage ? (u) => u.editUrl : undefined}
                    rowLabel={(u) => u.name}
                    empty={
                        <EmptyState
                            icon={<Users className="size-6" aria-hidden />}
                            title="Nessun utente trovato"
                            description={active ? 'Prova a modificare i filtri di ricerca.' : undefined}
                        />
                    }
                />
                <Pagination page={users} noun="utenti" />
            </Card>
        </AppShell>
    );
}
