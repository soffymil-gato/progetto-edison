import { Link } from '@inertiajs/react';
import { ArrowRight, Award, BadgeCheck, ChevronDown, Headset, Lightbulb, Lock, Mail, MapPin, Phone, ShieldCheck, Sparkles } from 'lucide-react';

import { ButtonLink } from '@/design-system/Button';
import { MultilineTxt, Txt } from '@/design-system/Placeholder';
import { CategoryArt } from '@/features/public/CategoryArt';
import { EnergyField } from '@/features/public/EnergyField';
import { Reveal } from '@/features/public/Reveal';
import { Section, SectionHeading } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';
import type { CompanySettings } from '@/lib/types';
import { useShared } from '@/lib/useShared';

type Item = { title: string; text: string };
type Props = {
    seo: Seo;
    content: {
        hero: { eyebrow: string; title: string; subtitle: string };
        about: { title: string; intro: string };
        products_intro: string;
        services_intro: string;
        why: { title: string; items: Item[] };
        portal: { title: string; text: string; points: string[] };
    };
    products: { name: string; description: string }[];
    services: { name: string; description: string }[];
    contactsPreview: CompanySettings;
};

const valueIcons = [ShieldCheck, Award, Lightbulb, BadgeCheck, Headset];

export default function Home({ seo, content, products, services, contactsPreview }: Props) {
    const { urls } = useShared();

    return (
        <PublicLayout seo={seo} tone="dark">
            {/* Hero */}
            <section className="relative isolate flex min-h-[100svh] items-center overflow-hidden bg-brand-950 text-white">
                <EnergyField className="absolute inset-0 -z-10" />
                <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgb(4_15_38/0.92)_0%,rgb(4_15_38/0.55)_55%,rgb(4_15_38/0.1)_100%)]" aria-hidden />
                <div className="mx-auto grid w-full max-w-7xl gap-12 px-4 pt-32 pb-20 sm:px-6 lg:grid-cols-[1.25fr_1fr] lg:items-center lg:px-8">
                    <div>
                        <p className="inline-flex animate-fade-up items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-medium tracking-[0.14em] text-spark-300 uppercase backdrop-blur">
                            <Sparkles className="size-3.5" aria-hidden />
                            {content.hero.eyebrow}
                        </p>
                        <h1 className="mt-6 animate-fade-up text-[2.6rem] leading-[1.05] font-semibold text-white [animation-delay:80ms] sm:text-6xl lg:text-7xl">{content.hero.title}</h1>
                        <p className="mt-6 max-w-xl animate-fade-up text-lg leading-relaxed text-white/75 [animation-delay:160ms] sm:text-xl">
                            <Txt value={content.hero.subtitle} />
                        </p>
                        <div className="mt-10 flex animate-fade-up flex-col gap-3 [animation-delay:240ms] sm:flex-row">
                            <ButtonLink href={urls.prodotti ?? '#'} size="lg" variant="inverse" icon={<ArrowRight className="size-4" aria-hidden />}>
                                Scopri i prodotti
                            </ButtonLink>
                            <ButtonLink href={urls.contatti ?? '#'} size="lg" variant="ghost" className="text-white ring-1 ring-white/25 hover:bg-white/10 hover:text-white">
                                Contattaci
                            </ButtonLink>
                        </div>
                    </div>

                    <div className="hidden animate-fade-up [animation-delay:320ms] lg:block">
                        <div className="rounded-panel border border-white/10 bg-white/[0.06] p-7 shadow-[0_30px_80px_-30px_rgb(0_0_0/0.6)] backdrop-blur-xl">
                            <div className="flex items-center gap-3">
                                <span className="inline-flex size-11 items-center justify-center rounded-xl bg-brand-600 shadow-glow">
                                    <Lock className="size-5" aria-hidden />
                                </span>
                                <div>
                                    <p className="font-display text-lg font-semibold text-white">{content.portal.title}</p>
                                    <p className="text-sm text-white/60">Accesso riservato ai clienti autorizzati</p>
                                </div>
                            </div>
                            <ul className="mt-6 space-y-3">
                                {content.portal.points.map((point) => (
                                    <li key={point} className="flex items-center gap-3 text-sm text-white/80">
                                        <BadgeCheck className="size-4.5 shrink-0 text-spark-400" aria-hidden />
                                        {point}
                                    </li>
                                ))}
                            </ul>
                            <a href={urls.portalLogin} className="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-spark-300 hover:text-white">
                                Accedi all'area clienti <ArrowRight className="size-4" aria-hidden />
                            </a>
                        </div>
                    </div>
                </div>
                <a href="#chi-siamo" className="absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-1 text-xs tracking-[0.2em] text-white/50 uppercase hover:text-white sm:flex">
                    Scopri
                    <ChevronDown className="size-4 animate-bounce" aria-hidden />
                </a>
            </section>

            {/* Chi siamo */}
            <Section id="chi-siamo">
                <div className="grid gap-12 lg:grid-cols-[1.1fr_1fr] lg:items-end">
                    <SectionHeading eyebrow="Chi siamo" title={content.about.title} intro={<Txt value={content.about.intro} />} />
                    <Reveal className="lg:justify-self-end">
                        <Link href={urls.azienda ?? '#'} className="group inline-flex items-center gap-3 rounded-full border border-line-strong px-5 py-3 font-medium text-fg transition-colors hover:border-brand-300 hover:bg-brand-50">
                            Storia, mission e valori
                            <span className="inline-flex size-8 items-center justify-center rounded-full bg-brand-600 text-white transition-transform group-hover:translate-x-0.5">
                                <ArrowRight className="size-4" aria-hidden />
                            </span>
                        </Link>
                    </Reveal>
                </div>
            </Section>

            {/* Prodotti */}
            <Section tone="muted">
                <div className="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <SectionHeading eyebrow="Prodotti" title="Le nostre categorie" intro={<Txt value={content.products_intro} />} />
                    <Reveal>
                        <ButtonLink href={urls.prodotti ?? '#'} variant="secondary" icon={<ArrowRight className="size-4" aria-hidden />}>
                            Tutti i prodotti
                        </ButtonLink>
                    </Reveal>
                </div>
                <ul className="mt-14 grid gap-6 md:grid-cols-3">
                    {products.map((product, index) => (
                        <Reveal as="li" key={index} delay={index * 90}>
                            <article className="group h-full overflow-hidden rounded-panel border border-line bg-surface shadow-card transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-elevated">
                                <CategoryArt index={index} className="aspect-[16/10]" />
                                <div className="p-6">
                                    <h3 className="text-xl font-semibold text-ink">
                                        <Txt value={product.name} />
                                    </h3>
                                    <p className="mt-2 leading-relaxed text-fg-muted">
                                        <Txt value={product.description} />
                                    </p>
                                </div>
                            </article>
                        </Reveal>
                    ))}
                </ul>
            </Section>

            {/* Servizi */}
            <Section>
                <div className="grid gap-12 lg:grid-cols-[1fr_1.3fr]">
                    <div className="lg:sticky lg:top-28 lg:self-start">
                        <SectionHeading eyebrow="Servizi" title="Servizi per i nostri clienti" intro={<Txt value={content.services_intro} />} />
                        <Reveal className="mt-8">
                            <ButtonLink href={urls.servizi ?? '#'} variant="secondary" icon={<ArrowRight className="size-4" aria-hidden />}>
                                Scopri i servizi
                            </ButtonLink>
                        </Reveal>
                    </div>
                    <ol className="space-y-4">
                        {services.map((service, index) => (
                            <Reveal as="li" key={index} delay={index * 80}>
                                <div className="flex gap-5 rounded-panel border border-line bg-surface p-6 transition-colors hover:border-brand-200">
                                    <span className="font-display text-3xl font-semibold text-brand-200 tabular">{String(index + 1).padStart(2, '0')}</span>
                                    <div>
                                        <h3 className="text-lg font-semibold text-ink">
                                            <Txt value={service.name} />
                                        </h3>
                                        <p className="mt-1.5 leading-relaxed text-fg-muted">
                                            <Txt value={service.description} />
                                        </p>
                                    </div>
                                </div>
                            </Reveal>
                        ))}
                    </ol>
                </div>
            </Section>

            {/* Perché Edison */}
            <Section tone="dark">
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_24rem_at_10%_0%,rgb(42_111_255/0.35),transparent_60%)]" aria-hidden />
                <div className="relative">
                    <SectionHeading eyebrow="Perché sceglierci" title={content.why.title} align="center" dark />
                    <ul className="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        {content.why.items.map((item, index) => {
                            const Icon = valueIcons[index % valueIcons.length] ?? ShieldCheck;
                            return (
                                <Reveal as="li" key={item.title} delay={index * 70}>
                                    <div className="h-full rounded-panel border border-white/10 bg-white/[0.04] p-6 transition-colors hover:border-spark-400/40 hover:bg-white/[0.07]">
                                        <span className="inline-flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-glow">
                                            <Icon className="size-5 text-white" aria-hidden />
                                        </span>
                                        <h3 className="mt-5 text-lg font-semibold text-white">{item.title}</h3>
                                        <p className="mt-2 text-sm leading-relaxed text-white/65">
                                            <Txt value={item.text} />
                                        </p>
                                    </div>
                                </Reveal>
                            );
                        })}
                    </ul>
                </div>
            </Section>

            {/* Area clienti */}
            <Section>
                <Reveal>
                    <div className="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 px-6 py-12 text-white sm:px-12 sm:py-16">
                        <div className="pointer-events-none absolute -top-24 -right-24 size-80 rounded-full bg-spark-400/25 blur-3xl" aria-hidden />
                        <div className="relative grid gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                            <div>
                                <h2 className="text-3xl font-semibold text-white sm:text-4xl">{content.portal.title}</h2>
                                <p className="mt-4 max-w-xl text-lg leading-relaxed text-white/75">{content.portal.text}</p>
                                <a href={urls.portalLogin} className="mt-8 inline-flex h-12 items-center gap-2 rounded-control bg-white px-6 font-semibold text-brand-900 transition-colors hover:bg-brand-50">
                                    <Lock className="size-4" aria-hidden />
                                    Accedi all'area clienti
                                </a>
                            </div>
                            <ul className="grid gap-3 sm:grid-cols-2">
                                {content.portal.points.map((point) => (
                                    <li key={point} className="rounded-2xl border border-white/15 bg-white/[0.07] p-4 text-sm font-medium text-white/90 backdrop-blur">
                                        <BadgeCheck className="mb-2 size-5 text-spark-300" aria-hidden />
                                        {point}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </Reveal>
            </Section>

            {/* Contatti */}
            <Section tone="muted">
                <div className="grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:items-center">
                    <SectionHeading eyebrow="Contatti" title="Come possiamo aiutarti?" intro="Scrivici per informazioni su prodotti e servizi." />
                    <Reveal>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <ContactTile icon={<MapPin className="size-5" aria-hidden />} label="Sede">
                                <MultilineTxt value={contactsPreview.registered_office} fallback="indirizzo" />
                            </ContactTile>
                            <ContactTile icon={<Phone className="size-5" aria-hidden />} label="Telefono">
                                {contactsPreview.phone ? <a href={`tel:${contactsPreview.phone.replace(/\s+/g, '')}`} className="hover:text-brand-700">{contactsPreview.phone}</a> : <Txt value={null} fallback="telefono" />}
                            </ContactTile>
                            <ContactTile icon={<Mail className="size-5" aria-hidden />} label="Email">
                                {contactsPreview.email ? <a href={`mailto:${contactsPreview.email}`} className="break-all hover:text-brand-700">{contactsPreview.email}</a> : <Txt value={null} fallback="email" />}
                            </ContactTile>
                            <Link href={urls.contatti ?? '#'} className="group flex flex-col justify-between rounded-panel bg-brand-600 p-5 text-white shadow-glow transition-colors hover:bg-brand-700">
                                <span className="text-sm text-white/75">Modulo di contatto</span>
                                <span className="mt-6 inline-flex items-center gap-2 font-display text-lg font-semibold">
                                    Scrivici <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" aria-hidden />
                                </span>
                            </Link>
                        </div>
                    </Reveal>
                </div>
            </Section>
        </PublicLayout>
    );
}

function ContactTile({ icon, label, children }: { icon: React.ReactNode; label: string; children: React.ReactNode }) {
    return (
        <div className="rounded-panel border border-line bg-surface p-5 shadow-card">
            <span className="inline-flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">{icon}</span>
            <p className="mt-4 text-xs font-semibold tracking-[0.14em] text-fg-subtle uppercase">{label}</p>
            <p className="mt-1 text-[0.9375rem] font-medium text-fg">{children}</p>
        </div>
    );
}
