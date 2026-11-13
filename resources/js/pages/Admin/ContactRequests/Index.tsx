import { router } from '@inertiajs/react';
import { Inbox } from 'lucide-react';

import { Badge } from '@/design-system/Badge';
import { Card } from '@/design-system/Card';
import { type Column, DataTable } from '@/design-system/DataTable';
import { EmptyState } from '@/design-system/EmptyState';
import { Select } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { Pagination } from '@/design-system/Pagination';
import { FilterBar } from '@/features/admin/FilterBar';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';
import type { Option, Paginated } from '@/lib/types';
import { useFilters } from '@/lib/useFilters';

type Row = { id: number; name: string; company: string | null; email: string; subject: string; status: string; statusLabel: string; createdAt: string; showUrl: string };

type Props = { requests: Paginated<Row>; filters: { q: string; status: string }; statuses: Option[]; urls: { index: string } };

export default function ContactRequestsIndex({ requests, filters, statuses, urls }: Props) {
    const { values, set, reset, active } = useFilters(urls.index, filters);

    const columns: Column<Row>[] = [
        { key: 'date', header: 'Ricevuta', cell: (r) => <time dateTime={r.createdAt} className="whitespace-nowrap text-fg-muted tabular">{formatDateTime(r.createdAt)}</time> },
        {
            key: 'from',
            header: 'Mittente',
            cell: (r) => (
                <div className="min-w-0">
                    <p className="truncate font-medium">{r.name}</p>
                    <p className="truncate text-xs text-fg-muted">{r.company ?? r.email}</p>
                </div>
            ),
        },
        { key: 'subject', header: 'Oggetto', cell: (r) => <span className="line-clamp-1">{r.subject}</span> },
        { key: 'status', header: 'Stato', cell: (r) => <Badge tone={r.status === 'new' ? 'warning' : 'success'} dot>{r.statusLabel}</Badge> },
    ];

    return (
        <AppShell title="Richieste di contatto">
            <PageHeader title="Richieste di contatto" description="Richieste inviate dal modulo del sito pubblico." />
            <Card>
                <FilterBar search={values.q} onSearch={(v) => set('q', v, true)} searchLabel="Cerca per nome, azienda, email o oggetto" active={active} onReset={reset}>
                    <Select value={values.status} onChange={(e) => set('status', e.target.value)} aria-label="Filtra per stato" className="lg:w-44">
                        <option value="">Tutte</option>
                        {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                    </Select>
                </FilterBar>
                <DataTable
                    caption="Richieste di contatto"
                    columns={columns}
                    rows={requests.data}
                    rowKey={(r) => r.id}
                    onRowClick={(r) => router.visit(r.showUrl)}
                    empty={<EmptyState icon={<Inbox className="size-6" aria-hidden />} title="Nessuna richiesta" description={active ? 'Nessun risultato con questi filtri.' : 'Le richieste inviate dal sito compariranno qui.'} />}
                />
                <Pagination page={requests} noun="richieste" />
            </Card>
        </AppShell>
    );
}
