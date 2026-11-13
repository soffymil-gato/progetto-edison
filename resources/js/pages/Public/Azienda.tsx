import { Award, BadgeCheck, Headset, Lightbulb, ShieldCheck } from 'lucide-react';

import { Txt } from '@/design-system/Placeholder';
import { Reveal } from '@/features/public/Reveal';
import { PageHero, Section, SectionHeading } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';

type Props = {
    seo: Seo;
    content: {
        title: string;
        intro: string;
        sections: { key: string; title: string; text: string }[];
        values_title: string;
        values: { title: string; text: string }[];
    };
};

const icons = [ShieldCheck, Award, Lightbulb, BadgeCheck, Headset];

export default function Azienda({ seo, content }: Props) {
    return (
        <PublicLayout seo={seo} tone="dark">
            <PageHero eyebrow="Azienda" title={content.title} intro={<Txt value={content.intro} />} />

            <Section>
                <div className="grid gap-x-16 gap-y-14 md:grid-cols-2">
                    {content.sections.map((section, index) => (
                        <Reveal key={section.key} delay={(index % 2) * 90}>
                            <article className="relative border-t border-line pt-8">
                                <span className="absolute -top-px left-0 h-0.5 w-14 bg-gradient-to-r from-brand-600 to-spark-400" aria-hidden />
                                <p className="font-display text-sm font-semibold text-brand-500 tabular">{String(index + 1).padStart(2, '0')}</p>
                                <h2 className="mt-3 text-2xl font-semibold text-ink sm:text-3xl">{section.title}</h2>
                                <p className="mt-4 text-lg leading-relaxed text-fg-muted">
                                    <Txt value={section.text} />
                                </p>
                            </article>
                        </Reveal>
                    ))}
                </div>
            </Section>

            <Section tone="muted">
                <SectionHeading eyebrow="Valori" title={content.values_title} />
                <ul className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {content.values.map((value, index) => {
                        const Icon = icons[index % icons.length] ?? ShieldCheck;
                        return (
                            <Reveal as="li" key={value.title} delay={index * 70}>
                                <div className="h-full rounded-panel border border-line bg-surface p-7 shadow-card">
                                    <Icon className="size-7 text-brand-600" aria-hidden />
                                    <h3 className="mt-5 text-xl font-semibold text-ink">{value.title}</h3>
                                    <p className="mt-2 leading-relaxed text-fg-muted">
                                        <Txt value={value.text} />
                                    </p>
                                </div>
                            </Reveal>
                        );
                    })}
                </ul>
            </Section>
        </PublicLayout>
    );
}
