import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { DescriptionList } from '@/design-system/DescriptionList';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';

type Props = {
    log: {
        id: number;
        occurredAt: string;
        action: string;
        actionLabel: string;
        actorType: string;
        actorLabel: string | null;
        subject: string | null;
        context: Record<string, unknown> | null;
        ipAddress: string | null;
        userAgent: string | null;
    };
    urls: { back: string };
};

export default function AuditShow({ log, urls }: Props) {
    return (
        <AppShell title={log.actionLabel}>
            <PageHeader
                back={{ href: urls.back, label: 'Registro attività' }}
                eyebrow={`Evento #${log.id}`}
                title={log.actionLabel}
                description={formatDateTime(log.occurredAt)}
            />
            <div className="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader title="Dettagli" />
                    <CardBody className="py-2">
                        <DescriptionList
                            items={[
                                {
                                    term: 'Codice azione',
                                    value: <span className="font-mono text-xs">{log.action}</span>,
                                },
                                {
                                    term: 'Attore',
                                    value:
                                        log.actorLabel ??
                                        (log.actorType === 'system'
                                            ? 'Sistema (comando da console)'
                                            : 'Utente non autenticato'),
                                },
                                { term: 'Tipo di attore', value: log.actorType },
                                { term: 'Risorsa', value: log.subject ?? '—' },
                                { term: 'Indirizzo IP', value: log.ipAddress ?? '—' },
                                {
                                    term: 'Browser',
                                    value: <span className="text-xs text-fg-muted">{log.userAgent ?? '—'}</span>,
                                },
                            ]}
                        />
                    </CardBody>
                </Card>
                <Card>
                    <CardHeader title="Contesto" description="Dati non sensibili registrati con l'evento." />
                    <CardBody>
                        {log.context ? (
                            <pre className="overflow-x-auto rounded-xl bg-brand-950 p-4 font-mono text-xs leading-relaxed text-brand-100">
                                {JSON.stringify(log.context, null, 2)}
                            </pre>
                        ) : (
                            <p className="text-sm text-fg-muted">Nessun dato aggiuntivo.</p>
                        )}
                    </CardBody>
                </Card>
            </div>
        </AppShell>
    );
}
