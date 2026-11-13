import { Lock } from 'lucide-react';

import { Txt } from '@/design-system/Placeholder';
import { CategoryArt } from '@/features/public/CategoryArt';
import { Reveal } from '@/features/public/Reveal';
import { PageHero, Section } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';
import { useShared } from '@/lib/useShared';

type Props = {
    seo: Seo;
    content: {
        title: string;
        intro: string;
        categories: { name: string; description: string }[];
        catalog_note: string;
    };
};

export default function Prodotti({ seo, content }: Props) {
    const { urls } = useShared();

    return (
        <PublicLayout seo={seo} tone="dark">
            <PageHero eyebrow="Prodotti" title={content.title} intro={<Txt value={content.intro} />} />
            <Section>
                <ul className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {content.categories.map((category, index) => (
                        <Reveal as="li" key={index} delay={index * 90}>
                            <article className="group flex h-full flex-col overflow-hidden rounded-panel border border-line bg-surface shadow-card transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-elevated">
                                <CategoryArt index={index} className="aspect-[4/3]" />
                                <div className="flex flex-1 flex-col p-7">
                                    <h2 className="text-2xl font-semibold text-ink">
                                        <Txt value={category.name} />
                                    </h2>
                                    <p className="mt-3 leading-relaxed text-fg-muted">
                                        <Txt value={category.description} />
                                    </p>
                                </div>
                            </article>
                        </Reveal>
                    ))}
                </ul>

                <Reveal className="mt-16">
                    <div className="flex flex-col items-start gap-6 rounded-panel border border-brand-100 bg-brand-50 p-7 sm:flex-row sm:items-center sm:justify-between sm:p-9">
                        <div className="flex gap-4">
                            <span className="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white">
                                <Lock className="size-5" aria-hidden />
                            </span>
                            <p className="max-w-2xl text-lg leading-relaxed text-brand-950">{content.catalog_note}</p>
                        </div>
                        <a
                            href={urls.portalLogin}
                            className="inline-flex h-11 shrink-0 items-center rounded-control bg-brand-600 px-5 font-medium text-white hover:bg-brand-700"
                        >
                            Area clienti
                        </a>
                    </div>
                </Reveal>
            </Section>
        </PublicLayout>
    );
}
