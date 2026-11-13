import type { ReactNode } from 'react';

import { cn } from '@/lib/cn';

import { Reveal } from './Reveal';

export function Section({ id, className, children, tone = 'light' }: { id?: string; className?: string; children: ReactNode; tone?: 'light' | 'muted' | 'dark' }) {
    return (
        <section
            id={id}
            className={cn(
                'relative scroll-mt-20 py-20 sm:py-28',
                tone === 'muted' && 'bg-canvas',
                tone === 'light' && 'bg-white',
                tone === 'dark' && 'overflow-hidden bg-brand-950 text-white',
                className,
            )}
        >
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">{children}</div>
        </section>
    );
}

export function SectionHeading({ eyebrow, title, intro, align = 'left', dark = false }: { eyebrow: string; title: ReactNode; intro?: ReactNode; align?: 'left' | 'center'; dark?: boolean }) {
    return (
        <Reveal className={cn('max-w-3xl', align === 'center' && 'mx-auto text-center')}>
            <p className={cn('inline-flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase', dark ? 'text-spark-300' : 'text-brand-600')}>
                <span className={cn('h-px w-6', dark ? 'bg-spark-400' : 'bg-brand-500')} aria-hidden />
                {eyebrow}
            </p>
            <h2 className={cn('mt-4 text-3xl leading-[1.15] font-semibold sm:text-4xl lg:text-[2.75rem]', dark ? 'text-white' : 'text-ink')}>{title}</h2>
            {intro && <p className={cn('mt-5 text-lg leading-relaxed', dark ? 'text-white/70' : 'text-fg-muted')}>{intro}</p>}
        </Reveal>
    );
}

/** Large page header of the inner pages. */
export function PageHero({ eyebrow, title, intro }: { eyebrow: string; title: string; intro?: ReactNode }) {
    return (
        <div className="relative overflow-hidden bg-brand-950 pt-36 pb-20 text-white sm:pt-44 sm:pb-24">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_24rem_at_90%_0%,rgb(42_111_255/0.4),transparent_65%),radial-gradient(30rem_18rem_at_0%_100%,rgb(60_200_255/0.12),transparent_70%)]" aria-hidden />
            <div className="pointer-events-none absolute inset-0 [background-image:linear-gradient(rgb(134_184_255/0.07)_1px,transparent_1px),linear-gradient(90deg,rgb(134_184_255/0.07)_1px,transparent_1px)] [background-size:48px_48px] [mask-image:linear-gradient(180deg,black,transparent)]" aria-hidden />
            <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p className="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.18em] text-spark-300 uppercase">
                    <span className="h-px w-6 bg-spark-400" aria-hidden />
                    {eyebrow}
                </p>
                <h1 className="mt-4 max-w-4xl animate-fade-up text-4xl leading-[1.1] font-semibold text-white sm:text-5xl lg:text-6xl">{title}</h1>
                {intro && <p className="mt-6 max-w-2xl animate-fade-up text-lg leading-relaxed text-white/75 [animation-delay:120ms]">{intro}</p>}
            </div>
        </div>
    );
}
