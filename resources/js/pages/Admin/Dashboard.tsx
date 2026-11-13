import { Link } from '@inertiajs/react';
import { ArrowRight, CircleCheck, CircleDashed, Database, Inbox, ScrollText, Users } from 'lucide-react';

import { Badge } from '@/design-system/Badge';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';
import { cn } from '@/lib/cn';
import { formatNumber, formatRelative } from '@/lib/format';
import { useStaff } from '@/lib/useShared';

type Check = { key: string; label: string; ok: boolean; missing: string[] };

type Props = {
    widgets: {
        contactRequests?: { new: number; last30Days: number; url: string };
        staff?: { active: number; invited: number; url: string };
        recentActivity?: {
            id: number;
            occurredAt: string;
            actionLabel: string;
            actorType: string;
            actorLabel: string | null;
            url: string;
        }[];
        auditUrl?: string;
    };
    readiness: { checks: Check[]; settingsUrl: string } | null;
};

const integration = [
    'Clienti e anagrafiche',
    'Catalogo, prezzi e disponibilità',
    'Ordini, preparazione e ritiri',
    'Statistiche e report sugli ordini',
];

export default function Dashboard({ widgets, readiness }: Props) {
    const staff = useStaff();
    const hour = new Date().getHours();
    const greeting = hour < 13 ? 'Buongiorno' : hour < 18 ? 'Buon pomeriggio' : 'Buonasera';

    return (
        <AppShell title="Panoramica">
            <PageHeader
                eyebrow="Panoramica"
                title={`${greeting}, ${staff?.name.split(' ')[0] ?? ''}`}
                description="Situazione della piattaforma e attività recenti."
            />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {widgets.contactRequests && (
                    <Stat
                        icon={<Inbox className="size-5" aria-hidden />}
                        label="Richieste di contatto da gestire"
                        value={widgets.contactRequests.new}
                        detail={`${formatNumber(widgets.contactRequests.last30Days)} ricevute negli ultimi 30 giorni`}
                        href={widgets.contactRequests.url}
                        highlight={widgets.contactRequests.new > 0}
                    />
                )}
                {widgets.staff && (
                    <Stat
                        icon={<Users className="size-5" aria-hidden />}
                        label="Utenti interni attivi"
                        value={widgets.staff.active}
                        detail={
                            widgets.staff.invited > 0
                                ? `${formatNumber(widgets.staff.invited)} in attesa di attivazione`
                                : 'Nessun invito in sospeso'
                        }
                        href={widgets.staff.url}
                    />
                )}
                <div className="rounded-card border border-dashed border-line-strong bg-surface/60 p-5">
                    <div className="flex items-center gap-2 text-sm font-medium text-fg">
                        <Database className="size-5 text-brand-500" aria-hidden />
                        Integrazione database aziendale
                    </div>
                    <p className="mt-2 text-sm text-fg-muted">
                        In attesa dell'analisi dello schema SQL Server. I seguenti indicatori non sono ancora
                        disponibili e non vengono simulati:
                    </p>
                    <ul className="mt-3 space-y-1.5 text-sm text-fg-muted">
                        {integration.map((item) => (
                            <li key={item} className="flex items-center gap-2">
                                <CircleDashed className="size-3.5 text-fg-subtle" aria-hidden />
                                {item}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>

            <div className="mt-6 grid gap-6 xl:grid-cols-[1.25fr_1fr]">
                {readiness && <ReadinessCard readiness={readiness} />}
                {widgets.recentActivity && (
                    <Card>
                        <CardHeader
                            title="Attività recenti"
                            actions={
                                widgets.auditUrl && (
                                    <Link
                                        href={widgets.auditUrl}
                                        className="inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:underline"
                                    >
                                        Registro completo <ArrowRight className="size-3.5" aria-hidden />
                                    </Link>
                                )
                            }
                        />
                        <ol className="divide-y divide-line">
                            {widgets.recentActivity.length === 0 && (
                                <li className="px-6 py-8 text-center text-sm text-fg-muted">
                                    Nessuna attività registrata.
                                </li>
                            )}
                            {widgets.recentActivity.map((event) => (
                                <li key={event.id}>
                                    <Link
                                        href={event.url}
                                        className="flex items-start gap-3 px-5 py-3.5 transition-colors hover:bg-canvas sm:px-6"
                                    >
                                        <ScrollText className="mt-0.5 size-4 shrink-0 text-fg-subtle" aria-hidden />
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-medium text-fg">{event.actionLabel}</p>
                                            <p className="truncate text-xs text-fg-muted">
                                                {event.actorLabel ??
                                                    (event.actorType === 'system'
                                                        ? 'Sistema (comando da console)'
                                                        : 'Utente non autenticato')}
                                            </p>
                                        </div>
                                        <time dateTime={event.occurredAt} className="shrink-0 text-xs text-fg-subtle">
                                            {formatRelative(event.occurredAt)}
                                        </time>
                                    </Link>
                                </li>
                            ))}
                        </ol>
                    </Card>
                )}
            </div>
        </AppShell>
    );
}

function Stat({
    icon,
    label,
    value,
    detail,
    href,
    highlight = false,
}: {
    icon: React.ReactNode;
    label: string;
    value: number;
    detail: string;
    href: string;
    highlight?: boolean;
}) {
    return (
        <Link
            href={href}
            className={cn(
                'group relative overflow-hidden rounded-card border bg-surface p-5 shadow-card transition-[border-color,box-shadow,transform] hover:-translate-y-0.5 hover:shadow-elevated',
                highlight ? 'border-brand-200' : 'border-line',
            )}
        >
            {highlight && (
                <span
                    className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-600 via-brand-500 to-spark-400"
                    aria-hidden
                />
            )}
            <div className="flex items-center justify-between">
                <span className="inline-flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                    {icon}
                </span>
                <ArrowRight
                    className="size-4 text-fg-subtle transition-transform group-hover:translate-x-0.5 group-hover:text-brand-700"
                    aria-hidden
                />
            </div>
            <p className="tabular mt-4 font-display text-3xl font-semibold text-fg">{formatNumber(value)}</p>
            <p className="mt-1 text-sm font-medium text-fg">{label}</p>
            <p className="mt-0.5 text-xs text-fg-muted">{detail}</p>
        </Link>
    );
}

function ReadinessCard({ readiness }: { readiness: { checks: Check[]; settingsUrl: string } }) {
    const done = readiness.checks.filter((check) => check.ok).length;
    const total = readiness.checks.length;
    const percent = Math.round((done / total) * 100);

    return (
        <Card>
            <CardHeader
                title="Prontezza al rilascio"
                description="Contenuti e dati aziendali da completare prima della pubblicazione."
                actions={
                    <Badge tone={done === total ? 'success' : 'warning'}>
                        {done === total ? 'Pronto' : `${done}/${total} completati`}
                    </Badge>
                }
            />
            <CardBody>
                <div
                    className="h-2 overflow-hidden rounded-full bg-line"
                    role="progressbar"
                    aria-valuenow={percent}
                    aria-valuemin={0}
                    aria-valuemax={100}
                    aria-label="Avanzamento"
                >
                    <div
                        className="h-full rounded-full bg-gradient-to-r from-brand-600 to-spark-400 transition-[width] duration-700"
                        style={{ width: `${percent}%` }}
                    />
                </div>
                <ul className="mt-5 space-y-3">
                    {readiness.checks.map((check) => (
                        <li key={check.key} className="flex gap-3">
                            {check.ok ? (
                                <CircleCheck className="mt-0.5 size-4.5 shrink-0 text-success-600" aria-hidden />
                            ) : (
                                <CircleDashed className="mt-0.5 size-4.5 shrink-0 text-warning-600" aria-hidden />
                            )}
                            <div className="min-w-0 text-sm">
                                <p className="font-medium text-fg">
                                    {check.label}
                                    <span className="sr-only">{check.ok ? ': completato' : ': da completare'}</span>
                                </p>
                                {!check.ok && (
                                    <p className="mt-0.5 text-fg-muted">
                                        {check.key === 'content'
                                            ? `${check.missing.length} testi da ricevere da Edison (resources/content/site.php).`
                                            : `Mancano: ${check.missing.join(', ')}.`}
                                    </p>
                                )}
                            </div>
                        </li>
                    ))}
                </ul>
                <Link
                    href={readiness.settingsUrl}
                    className="mt-5 inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:underline"
                >
                    Completa le impostazioni <ArrowRight className="size-3.5" aria-hidden />
                </Link>
            </CardBody>
        </Card>
    );
}
