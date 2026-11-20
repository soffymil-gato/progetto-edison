import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, Lock, Mail, MapPin, Menu, Phone, X } from 'lucide-react';
import { Dialog } from 'radix-ui';
import { type ReactNode, useEffect, useState } from 'react';

import { BrandMark } from '@/design-system/BrandMark';
import { ButtonLink } from '@/design-system/Button';
import { MultilineTxt, Txt } from '@/design-system/Placeholder';
import { Toaster } from '@/design-system/Toaster';
import { cn } from '@/lib/cn';
import { routeMatches, useShared } from '@/lib/useShared';

export type Seo = { title: string; description: string | null };

/**
 * Layout of the public website: sticky header that condenses on scroll,
 * accessible mobile menu, complete footer with company data from settings.
 */
export function PublicLayout({
    seo,
    children,
    tone = 'light',
}: {
    seo: Seo;
    children: ReactNode;
    tone?: 'light' | 'dark';
}) {
    const { nav, urls, route } = useShared();
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 12);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    const overHero = tone === 'dark' && !scrolled;

    return (
        <>
            <Head title={seo.title}>
                {seo.description ? <meta head-key="description" name="description" content={seo.description} /> : null}
            </Head>
            <a
                href="#contenuto"
                className="sr-only z-[60] rounded-lg bg-white px-4 py-2 font-medium text-brand-800 focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
            >
                Vai al contenuto
            </a>

            <header
                className={cn(
                    'fixed inset-x-0 top-0 z-40 transition-[background-color,box-shadow,backdrop-filter] duration-300',
                    overHero
                        ? 'bg-transparent'
                        : 'border-b border-line/70 bg-white/85 shadow-[0_8px_30px_-20px_rgb(7_26_61/0.25)] backdrop-blur-xl',
                )}
            >
                <div
                    className={cn(
                        'mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 transition-[height] duration-300 sm:px-6 lg:px-8',
                        scrolled ? 'h-16' : 'h-20',
                    )}
                >
                    <Link href={urls.home ?? '/'} className="shrink-0 rounded-lg" aria-label="Torna alla home">
                        <BrandMark tone={overHero ? 'light' : 'dark'} />
                    </Link>

                    <nav aria-label="Navigazione principale" className="hidden lg:block">
                        <ul className="flex items-center gap-1">
                            {nav.map((item) => {
                                const active = routeMatches(route, item.match);
                                return (
                                    <li key={item.href}>
                                        <Link
                                            href={item.href}
                                            aria-current={active ? 'page' : undefined}
                                            className={cn(
                                                'relative rounded-full px-3.5 py-2 text-[0.9375rem] font-medium transition-colors',
                                                overHero
                                                    ? 'text-white/80 hover:text-white'
                                                    : 'text-fg-muted hover:text-brand-800',
                                                active && (overHero ? 'text-white' : 'text-brand-800'),
                                            )}
                                        >
                                            {item.label}
                                            {active && (
                                                <span
                                                    className={cn(
                                                        'absolute inset-x-3.5 -bottom-0.5 h-0.5 rounded-full',
                                                        overHero ? 'bg-spark-400' : 'bg-brand-600',
                                                    )}
                                                    aria-hidden
                                                />
                                            )}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </nav>

                    <div className="flex items-center gap-2">
                        <ButtonLink
                            href={urls.portalLogin ?? '#'}
                            external
                            variant={overHero ? 'inverse' : 'primary'}
                            size="sm"
                            className="hidden sm:inline-flex"
                            icon={<Lock className="size-3.5" aria-hidden />}
                        >
                            Area clienti
                        </ButtonLink>
                        <MobileMenu overHero={overHero} />
                    </div>
                </div>
            </header>

            <main id="contenuto" tabIndex={-1} className="outline-none">
                {children}
            </main>

            <Footer />
            <Toaster />
        </>
    );
}

function MobileMenu({ overHero }: { overHero: boolean }) {
    const { nav, urls, route } = useShared();
    const [open, setOpen] = useState(false);

    return (
        <Dialog.Root open={open} onOpenChange={setOpen}>
            <Dialog.Trigger
                className={cn(
                    'inline-flex size-11 items-center justify-center rounded-xl transition-colors lg:hidden',
                    overHero ? 'text-white hover:bg-white/10' : 'text-fg hover:bg-brand-50',
                )}
                aria-label="Apri il menu"
            >
                <Menu className="size-5.5" aria-hidden />
            </Dialog.Trigger>
            <Dialog.Portal>
                <Dialog.Overlay className="fixed inset-0 z-50 animate-fade-in bg-ink/50 backdrop-blur-sm lg:hidden" />
                <Dialog.Content className="fixed inset-y-0 right-0 z-50 flex w-[min(22rem,100%)] animate-fade-in flex-col bg-white shadow-elevated lg:hidden">
                    <div className="flex h-20 items-center justify-between border-b border-line px-5">
                        <Dialog.Title className="sr-only">Menu</Dialog.Title>
                        <BrandMark />
                        <Dialog.Close
                            className="inline-flex size-11 items-center justify-center rounded-xl hover:bg-brand-50"
                            aria-label="Chiudi il menu"
                        >
                            <X className="size-5.5" aria-hidden />
                        </Dialog.Close>
                    </div>
                    <nav aria-label="Navigazione principale" className="flex-1 overflow-y-auto px-3 py-4">
                        <ul className="flex flex-col gap-1">
                            {nav.map((item) => {
                                const active = routeMatches(route, item.match);
                                return (
                                    <li key={item.href}>
                                        <Link
                                            href={item.href}
                                            onClick={() => setOpen(false)}
                                            aria-current={active ? 'page' : undefined}
                                            className={cn(
                                                'flex items-center justify-between rounded-xl px-4 py-3.5 font-display text-lg font-medium',
                                                active ? 'bg-brand-50 text-brand-800' : 'text-fg hover:bg-canvas',
                                            )}
                                        >
                                            {item.label}
                                            <ArrowUpRight className="size-4 text-fg-subtle" aria-hidden />
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </nav>
                    <div className="border-t border-line p-5">
                        <ButtonLink
                            href={urls.portalLogin ?? '#'}
                            external
                            className="w-full"
                            size="lg"
                            icon={<Lock className="size-4" aria-hidden />}
                        >
                            Area clienti
                        </ButtonLink>
                    </div>
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}

function Footer() {
    const { company, nav, urls, appName } = useShared();
    const year = new Date().getFullYear();

    return (
        <footer className="relative overflow-hidden bg-ink text-white/75">
            <div
                className="pointer-events-none absolute inset-0 bg-[radial-gradient(60rem_30rem_at_85%_-10%,rgb(42_111_255/0.22),transparent_60%)]"
                aria-hidden
            />
            <div className="relative mx-auto max-w-7xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">
                <div className="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
                    <div className="max-w-sm">
                        <BrandMark tone="light" />
                        <p className="mt-5 text-sm leading-relaxed text-white/70">
                            <Txt
                                value={company.short_description}
                                fallback="descrizione breve dell'azienda (Impostazioni)"
                            />
                        </p>
                    </div>

                    <div>
                        <h2 className="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">Navigazione</h2>
                        <ul className="mt-4 space-y-2.5 text-sm">
                            {nav.map((item) => (
                                <li key={item.href}>
                                    <Link href={item.href} className="transition-colors hover:text-white">
                                        {item.label}
                                    </Link>
                                </li>
                            ))}
                            <li>
                                <a href={urls.portalLogin} className="transition-colors hover:text-white">
                                    Area clienti
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 className="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">
                            Informazioni legali
                        </h2>
                        <ul className="mt-4 space-y-2.5 text-sm">
                            <li>
                                <Link href={urls.privacy ?? '#'} className="transition-colors hover:text-white">
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link href={urls.cookie ?? '#'} className="transition-colors hover:text-white">
                                    Cookie Policy
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 className="text-xs font-semibold tracking-[0.16em] text-white/50 uppercase">Contatti</h2>
                        <ul className="mt-4 space-y-3 text-sm">
                            <li className="flex gap-3">
                                <MapPin className="mt-0.5 size-4 shrink-0 text-spark-400" aria-hidden />
                                <MultilineTxt
                                    value={company.registered_office}
                                    fallback="indirizzo della sede (Impostazioni)"
                                />
                            </li>
                            <li className="flex gap-3">
                                <Phone className="mt-0.5 size-4 shrink-0 text-spark-400" aria-hidden />
                                {company.phone ? (
                                    <a href={`tel:${company.phone.replace(/\s+/g, '')}`} className="hover:text-white">
                                        {company.phone}
                                    </a>
                                ) : (
                                    <Txt value={null} fallback="telefono (Impostazioni)" />
                                )}
                            </li>
                            <li className="flex gap-3">
                                <Mail className="mt-0.5 size-4 shrink-0 text-spark-400" aria-hidden />
                                {company.email ? (
                                    <a href={`mailto:${company.email}`} className="break-all hover:text-white">
                                        {company.email}
                                    </a>
                                ) : (
                                    <Txt value={null} fallback="email (Impostazioni)" />
                                )}
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="mt-14 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/55 md:flex-row md:items-center md:justify-between">
                    <p>
                        © {year} <Txt value={company.legal_name} fallback="ragione sociale" />
                        {' · '}P.IVA <Txt value={company.vat_number} fallback="partita IVA" />
                        {company.rea && <> · REA {company.rea}</>}
                        {company.share_capital && <> · Capitale sociale {company.share_capital}</>}
                    </p>
                    <p>{appName} — tutti i diritti riservati</p>
                </div>
            </div>
        </footer>
    );
}
