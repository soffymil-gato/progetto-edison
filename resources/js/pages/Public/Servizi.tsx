import { CalendarClock, MapPin, PackageCheck } from 'lucide-react';

import { MultilineTxt, Txt } from '@/design-system/Placeholder';
import { Reveal } from '@/features/public/Reveal';
import { PageHero, Section } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';
import type { PickupSettings } from '@/lib/types';

type Props = {
    seo: Seo;
    content: { title: string; intro: string; items: { name: string; description: string }[]; ordering: { title: string; text: string } };
    pickup: PickupSettings;
};

export default function Servizi({ seo, content, pickup }: Props) {
    return (
        <PublicLayout seo={seo} tone="dark">
            <PageHero eyebrow="Servizi" title={content.title} intro={<Txt value={content.intro} />} />
            <Section>
                <ol className="divide-y divide-line border-y border-line">
                    {content.items.map((item, index) => (
                        <Reveal as="li" key={index}>
                            <div className="grid gap-4 py-10 md:grid-cols-[6rem_1fr_1.4fr] md:gap-10">
                                <span className="font-display text-5xl font-semibold text-brand-100 tabular">{String(index + 1).padStart(2, '0')}</span>
                                <h2 className="text-2xl font-semibold text-ink">
                                    <Txt value={item.name} />
                                </h2>
                                <p className="text-lg leading-relaxed text-fg-muted">
                                    <Txt value={item.description} />
                                </p>
                            </div>
                        </Reveal>
                    ))}
                </ol>
            </Section>

            <Section tone="muted">
                <Reveal>
                    <div className="grid gap-10 rounded-[2rem] bg-surface p-8 shadow-card sm:p-12 lg:grid-cols-[1.2fr_1fr]">
                        <div>
                            <span className="inline-flex size-12 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-glow">
                                <PackageCheck className="size-6" aria-hidden />
                            </span>
                            <h2 className="mt-6 text-3xl font-semibold text-ink">{content.ordering.title}</h2>
                            <p className="mt-4 text-lg leading-relaxed text-fg-muted">{content.ordering.text}</p>
                        </div>
                        <div className="space-y-5 rounded-panel bg-canvas p-6">
                            <p className="flex gap-3">
                                <MapPin className="mt-0.5 size-5 shrink-0 text-brand-600" aria-hidden />
                                <span>
                                    <span className="block text-sm font-semibold text-fg">Sede di ritiro</span>
                                    <span className="text-fg-muted">
                                        <MultilineTxt value={pickup.address} fallback="indirizzo della sede di ritiro" />
                                    </span>
                                </span>
                            </p>
                            <p className="flex gap-3">
                                <CalendarClock className="mt-0.5 size-5 shrink-0 text-brand-600" aria-hidden />
                                <span>
                                    <span className="block text-sm font-semibold text-fg">Orari di ritiro</span>
                                    <span className="text-fg-muted">
                                        <MultilineTxt value={pickup.hours} fallback="orari di ritiro" />
                                    </span>
                                </span>
                            </p>
                        </div>
                    </div>
                </Reveal>
            </Section>
        </PublicLayout>
    );
}
