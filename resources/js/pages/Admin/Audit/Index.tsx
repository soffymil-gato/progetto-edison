import { ScrollText } from 'lucide-react';

import { Badge } from '@/design-system/Badge';
import { Card } from '@/design-system/Card';
import { type Column, DataTable } from '@/design-system/DataTable';
import { EmptyState } from '@/design-system/EmptyState';
import { Input, Select } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { Pagination } from '@/design-system/Pagination';
import { FilterBar } from '@/features/admin/FilterBar';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';
import type { Option, Paginated } from '@/lib/types';
import { useFilters } from '@/lib/useFilters';

type LogRow = {
    id: number;
    occurredAt: string;
    action: string;
    actionLabel: string;
    actorType: string;
    actorLabel: string | null;
    subject: string | null;
    showUrl: string;
};

type Props = {
    logs: Paginated<LogRow>;
    filters: { q: string; action: string; actor_type: string; from: string; to: string };
    actions: Option[];
    urls: { index: string };
};

const actorTypes: Option[] = [
    { value: 'staff', label: 'Personale' },
    { value: 'customer', label: 'Cliente' },
    { value: 'anonymous', label: 'Non autenticato' },
    { value: 'system', label: 'Sistema' },
];

export default function AuditIndex({ logs, filters, actions, urls }: Props) {
    const { values, set, reset, active } = useFilters(urls.index, filters);

    const columns: Column<LogRow>[] = [
        {
            key: 'when',
            header: 'Data e ora',
            cell: (l) => (
                <time dateTime={l.occurredAt} className="tabular whitespace-nowrap text-fg-muted">
                    {formatDateTime(l.occurredAt)}
                </time>
            ),
        },
        { key: 'action', header: 'Azione', cell: (l) => <span className="font-medium">{l.actionLabel}</span> },
        {
            key: 'actor',
            header: 'Attore',
            cell: (l) => (
                <span className="flex items-center gap-2">
                    <Badge tone={l.actorType === 'staff' ? 'brand' : 'neutral'}>
                        {actorTypes.find((a) => a.value === l.actorType)?.label ?? l.actorType}
                    </Badge>
                    <span className="truncate text-fg-muted">{l.actorLabel ?? '—'}</span>
                </span>
            ),
        },
        {
            key: 'subject',
            header: 'Risorsa',
            hideOnMobile: true,
            cell: (l) => <span className="font-mono text-xs text-fg-muted">{l.subject ?? '—'}</span>,
        },
    ];

    return (
        <AppShell title="Registro attività">
            <PageHeader
                title="Registro attività"
                description="Accessi e operazioni rilevanti. Il registro è in sola aggiunta: le voci non possono essere modificate né cancellate."
            />
            <Card>
                <FilterBar
                    search={values.q}
                    onSearch={(v) => set('q', v, true)}
                    searchLabel="Cerca per email dell'attore"
                    active={active}
                    onReset={reset}
                >
                    <Select
                        value={values.action}
                        onChange={(e) => set('action', e.target.value)}
                        aria-label="Filtra per azione"
                        className="lg:w-56"
                    >
                        <option value="">Tutte le azioni</option>
                        {actions.map((a) => (
                            <option key={a.value} value={a.value}>
                                {a.label}
                            </option>
                        ))}
                    </Select>
                    <Select
                        value={values.actor_type}
                        onChange={(e) => set('actor_type', e.target.value)}
                        aria-label="Filtra per tipo di attore"
                        className="lg:w-44"
                    >
                        <option value="">Tutti gli attori</option>
                        {actorTypes.map((a) => (
                            <option key={a.value} value={a.value}>
                                {a.label}
                            </option>
                        ))}
                    </Select>
                    <Input
                        type="date"
                        value={values.from}
                        onChange={(e) => set('from', e.target.value)}
                        aria-label="Dal giorno"
                        className="lg:w-40"
                    />
                    <Input
                        type="date"
                        value={values.to}
                        onChange={(e) => set('to', e.target.value)}
                        aria-label="Al giorno"
                        className="lg:w-40"
                    />
                </FilterBar>
                <DataTable
                    caption="Registro delle attività"
                    columns={columns}
                    rows={logs.data}
                    rowKey={(l) => l.id}
                    rowHref={(l) => l.showUrl}
                    rowLabel={(l) => `evento ${l.id}`}
                    empty={
                        <EmptyState
                            icon={<ScrollText className="size-6" aria-hidden />}
                            title="Nessuna attività trovata"
                            description={active ? 'Prova a modificare i filtri.' : undefined}
                        />
                    }
                />
                <Pagination page={logs} noun="eventi" />
            </Card>
        </AppShell>
    );
}
