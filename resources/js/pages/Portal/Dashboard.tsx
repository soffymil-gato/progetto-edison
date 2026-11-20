import { Link } from '@inertiajs/react';
import { Boxes, CalendarClock, ClipboardList, FileText, Headset, MapPin, ShoppingCart, UserCog } from 'lucide-react';

import { Alert } from '@/design-system/Alert';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { MultilineTxt, Txt } from '@/design-system/Placeholder';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';
import type { PickupSettings } from '@/lib/types';
import { useShared } from '@/lib/useShared';

type Props = {
    account: { name: string; email: string; username: string | null; customerRef: string; lastLoginAt: string | null };
    pickup: PickupSettings;
    company: { phone: string | null; email: string | null; officeHours: string | null };
};

/** Modules that need the integration with the company SQL Server database. Shown as information, not as buttons. */
const upcoming = [
    {
        icon: Boxes,
        title: 'Catalogo e listino',
        text: 'Prodotti, prezzi, IVA e disponibilità letti dal database aziendale.',
    },
    {
        icon: ShoppingCart,
        title: 'Carrello e ordini',
        text: "Ordini confermati al momento dell'invio, con ritiro in sede.",
    },
    {
        icon: ClipboardList,
        title: 'Storico e stato ordini',
        text: 'Confermato, in preparazione, pronto per il ritiro, ritirato.',
    },
    { icon: FileText, title: 'Documenti', text: 'Documenti riservati al tuo account, scaricabili in modo protetto.' },
];

export default function Dashboard({ account, pickup, company }: Props) {
    const { urls } = useShared();
    const firstName = account.name.split(' ')[0];

    return (
        <AppShell title="Dashboard">
            <section className="relative overflow-hidden rounded-panel bg-brand-950 px-6 py-8 text-white sm:px-10 sm:py-10">
                <div
                    className="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_20rem_at_100%_0%,rgb(42_111_255/0.45),transparent_60%)]"
                    aria-hidden
                />
                <div className="relative flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm font-medium text-spark-300">Area clienti</p>
                        <h1 className="mt-1 text-3xl font-semibold text-white sm:text-4xl">Ciao, {firstName}</h1>
                        <p className="mt-2 text-white/70">Ultimo accesso: {formatDateTime(account.lastLoginAt)}</p>
                    </div>
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-1 text-sm">
                        <dt className="text-white/55">Riferimento cliente</dt>
                        <dd className="tabular font-medium">{account.customerRef}</dd>
                        <dt className="text-white/55">{account.username ? 'Nome utente' : 'Email'}</dt>
                        <dd className="truncate font-medium">{account.username ?? account.email}</dd>
                    </dl>
                </div>
            </section>

            <Alert tone="info" title="Catalogo e ordini in attivazione" className="mt-6">
                Queste funzioni saranno disponibili dopo il collegamento con il database aziendale. I dati non vengono
                simulati: fino ad allora qui trovi solo informazioni reali sul tuo account e sul ritiro.
            </Alert>

            <div className="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                <Card>
                    <CardHeader title="Prossime funzionalità" description="Moduli previsti per l'area clienti." />
                    <CardBody>
                        <ul className="grid gap-4 sm:grid-cols-2">
                            {upcoming.map(({ icon: Icon, title, text }) => (
                                <li
                                    key={title}
                                    className="flex gap-3 rounded-xl border border-dashed border-line-strong p-4"
                                >
                                    <Icon className="mt-0.5 size-5 shrink-0 text-brand-500" aria-hidden />
                                    <div>
                                        <p className="font-medium text-fg">{title}</p>
                                        <p className="mt-0.5 text-sm text-fg-muted">{text}</p>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </CardBody>
                </Card>

                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader title="Ritiro presso la sede" />
                        <CardBody className="space-y-4 text-sm">
                            <p className="flex gap-3">
                                <MapPin className="mt-0.5 size-4.5 shrink-0 text-brand-600" aria-hidden />
                                <MultilineTxt value={pickup.address} fallback="indirizzo della sede di ritiro" />
                            </p>
                            <p className="flex gap-3">
                                <CalendarClock className="mt-0.5 size-4.5 shrink-0 text-brand-600" aria-hidden />
                                <MultilineTxt value={pickup.hours} fallback="orari di ritiro" />
                            </p>
                            {pickup.notes && (
                                <p className="rounded-lg bg-canvas px-3 py-2.5 whitespace-pre-line text-fg-muted">
                                    {pickup.notes}
                                </p>
                            )}
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader title="Assistenza" />
                        <CardBody className="space-y-3 text-sm">
                            <p className="flex gap-3">
                                <Headset className="mt-0.5 size-4.5 shrink-0 text-brand-600" aria-hidden />
                                <span className="flex flex-col items-start gap-1.5">
                                    {company.phone ? (
                                        <a
                                            className="font-medium text-brand-700 hover:underline"
                                            href={`tel:${company.phone.replace(/\s+/g, '')}`}
                                        >
                                            {company.phone}
                                        </a>
                                    ) : (
                                        <Txt value={null} fallback="telefono" />
                                    )}
                                    {company.email ? (
                                        <a
                                            className="font-medium text-brand-700 hover:underline"
                                            href={`mailto:${company.email}`}
                                        >
                                            {company.email}
                                        </a>
                                    ) : (
                                        <Txt value={null} fallback="email" />
                                    )}
                                </span>
                            </p>
                            <p className="pl-7.5 text-fg-muted">
                                <MultilineTxt value={company.officeHours} fallback="orari degli uffici" />
                            </p>
                        </CardBody>
                    </Card>

                    <Link
                        href={urls.profile ?? '#'}
                        className="group flex items-center gap-3 rounded-card border border-line bg-surface px-5 py-4 shadow-card transition-colors hover:border-brand-300"
                    >
                        <UserCog className="size-5 text-brand-600" aria-hidden />
                        <span className="flex-1 text-sm font-medium text-fg">Profilo e sicurezza</span>
                        <span
                            className="text-sm text-brand-700 transition-transform group-hover:translate-x-0.5"
                            aria-hidden
                        >
                            →
                        </span>
                    </Link>
                </div>
            </div>
        </AppShell>
    );
}
