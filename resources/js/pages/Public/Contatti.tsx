import { CalendarClock, Clock, ExternalLink, Mail, MapPin, Phone } from 'lucide-react';

import { MultilineTxt, Txt } from '@/design-system/Placeholder';
import { ContactForm } from '@/features/public/ContactForm';
import { PageHero, Section } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';
import type { PickupSettings } from '@/lib/types';
import { useShared } from '@/lib/useShared';

type Props = { seo: Seo; content: { title: string; intro: string }; pickup: PickupSettings; storeUrl: string };

export default function Contatti({ seo, content, pickup, storeUrl }: Props) {
    const { company } = useShared();
    // No embedded third-party map (it would set cookies): a link opens the map provider on request.
    const mapUrl = company.registered_office
        ? `https://www.openstreetmap.org/search?query=${encodeURIComponent(company.registered_office)}`
        : null;

    return (
        <PublicLayout seo={seo} tone="dark">
            <PageHero eyebrow="Contatti" title={content.title} intro={content.intro} />
            <Section>
                <div className="grid gap-12 lg:grid-cols-[1fr_1.5fr]">
                    <aside className="space-y-4">
                        <InfoCard icon={<MapPin className="size-5" aria-hidden />} title="Sede">
                            <MultilineTxt value={company.registered_office} fallback="indirizzo della sede" />
                            {mapUrl && (
                                <a
                                    href={mapUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:underline"
                                >
                                    Apri la mappa <ExternalLink className="size-3.5" aria-hidden />
                                    <span className="sr-only">(si apre in una nuova scheda, sito esterno)</span>
                                </a>
                            )}
                        </InfoCard>
                        <InfoCard icon={<Phone className="size-5" aria-hidden />} title="Telefono">
                            {company.phone ? (
                                <a href={`tel:${company.phone.replace(/\s+/g, '')}`} className="hover:text-brand-700">
                                    {company.phone}
                                </a>
                            ) : (
                                <Txt value={null} fallback="telefono" />
                            )}
                        </InfoCard>
                        <InfoCard icon={<Mail className="size-5" aria-hidden />} title="Email">
                            {company.email ? (
                                <a href={`mailto:${company.email}`} className="break-all hover:text-brand-700">
                                    {company.email}
                                </a>
                            ) : (
                                <Txt value={null} fallback="email" />
                            )}
                            {company.pec && (
                                <span className="mt-1 block text-sm text-fg-muted">PEC: {company.pec}</span>
                            )}
                        </InfoCard>
                        <InfoCard icon={<Clock className="size-5" aria-hidden />} title="Orari degli uffici">
                            <MultilineTxt value={company.office_hours} fallback="orari degli uffici" />
                        </InfoCard>
                        <InfoCard icon={<CalendarClock className="size-5" aria-hidden />} title="Ritiro ordini">
                            <MultilineTxt value={pickup.hours} fallback="orari di ritiro" />
                        </InfoCard>
                    </aside>

                    <div className="rounded-[2rem] border border-line bg-surface p-6 shadow-card sm:p-10">
                        <h2 className="text-2xl font-semibold text-ink">Scrivici</h2>
                        <p className="mt-2 mb-8 text-fg-muted">
                            Compila il modulo: la richiesta arriva direttamente al nostro personale.
                        </p>
                        <ContactForm storeUrl={storeUrl} />
                    </div>
                </div>
            </Section>
        </PublicLayout>
    );
}

function InfoCard({ icon, title, children }: { icon: React.ReactNode; title: string; children: React.ReactNode }) {
    return (
        <div className="flex gap-4 rounded-panel border border-line bg-canvas/60 p-5">
            <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-700 shadow-card">
                {icon}
            </span>
            <div className="min-w-0">
                <p className="text-sm font-semibold text-fg">{title}</p>
                <div className="mt-1 text-[0.9375rem] text-fg-muted">{children}</div>
            </div>
        </div>
    );
}
