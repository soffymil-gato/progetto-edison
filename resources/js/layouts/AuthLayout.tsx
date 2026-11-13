import { Head } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck } from 'lucide-react';
import type { ReactNode } from 'react';

import { BrandMark } from '@/design-system/BrandMark';
import { Toaster } from '@/design-system/Toaster';
import { EnergyField } from '@/features/public/EnergyField';
import { pageTitle } from '@/lib/title';
import { useShared } from '@/lib/useShared';

/**
 * Split-screen layout of the authentication pages (customer area and admin
 * panel). The brand panel collapses into a compact header on small screens.
 */
export function AuthLayout({ title, description, children }: { title: string; description?: ReactNode; children: ReactNode }) {
    const { area, appName, urls } = useShared();
    const isAdmin = area === 'admin';

    return (
        <>
            <Head title={pageTitle(title, area, appName)} />
            <div className="grid min-h-dvh lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
                <aside className="relative hidden overflow-hidden bg-brand-950 text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
                    <EnergyField className="absolute inset-0 opacity-90" />
                    <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_40%,rgb(4_15_38/0.85))]" aria-hidden />
                    <div className="relative">
                        <BrandMark tone="light" />
                    </div>
                    <div className="relative max-w-md">
                        <p className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium tracking-wide text-spark-300 backdrop-blur">
                            <ShieldCheck className="size-3.5" aria-hidden />
                            {isAdmin ? 'Accesso riservato al personale' : 'Area riservata ai clienti autorizzati'}
                        </p>
                        <h2 className="mt-5 text-4xl leading-tight font-semibold text-white xl:text-5xl">{isAdmin ? 'Gestionale' : 'Area clienti'}</h2>
                        <p className="mt-4 text-base leading-relaxed text-white/70">
                            {isAdmin
                                ? 'Gestione di utenti, permessi, impostazioni aziendali e richieste dal sito.'
                                : 'Catalogo e listino unici, ordini confermati e ritiro presso la sede aziendale.'}
                        </p>
                    </div>
                </aside>

                <div className="flex min-h-dvh flex-col bg-canvas">
                    <div className="flex items-center justify-between px-5 py-5 sm:px-8">
                        <div className="lg:hidden">
                            <BrandMark />
                        </div>
                        {urls.publicHome && (
                            <a href={urls.publicHome} className="ml-auto inline-flex items-center gap-1.5 text-sm font-medium text-fg-muted hover:text-brand-700">
                                <ArrowLeft className="size-4" aria-hidden />
                                Torna al sito
                            </a>
                        )}
                    </div>
                    <main className="flex flex-1 items-center justify-center px-5 pb-12 sm:px-8">
                        <div className="w-full max-w-[26rem] animate-fade-up">
                            <p className="mb-2 text-xs font-semibold tracking-[0.16em] text-brand-600 uppercase lg:hidden">{isAdmin ? 'Gestionale' : 'Area clienti'}</p>
                            <h1 className="text-[1.75rem] leading-tight font-semibold text-fg">{title}</h1>
                            {description && <p className="mt-2 text-[0.9375rem] leading-relaxed text-fg-muted">{description}</p>}
                            <div className="mt-8">{children}</div>
                        </div>
                    </main>
                </div>
            </div>
            <Toaster />
        </>
    );
}
