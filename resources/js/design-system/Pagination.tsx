import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

import { cn } from '@/lib/cn';
import { formatNumber } from '@/lib/format';
import type { Paginated } from '@/lib/types';

export function Pagination<T>({ page, noun = 'risultati' }: { page: Paginated<T>; noun?: string }) {
    if (page.total === 0) return null;

    const numbered = page.links.slice(1, -1);

    return (
        <nav className="flex flex-col items-center justify-between gap-3 border-t border-line px-5 py-3.5 sm:flex-row" aria-label="Paginazione">
            <p className="text-sm text-fg-muted">
                {formatNumber(page.from ?? 0)}–{formatNumber(page.to ?? 0)} di <span className="font-medium text-fg">{formatNumber(page.total)}</span> {noun}
            </p>
            {page.last_page > 1 && (
                <div className="flex items-center gap-1">
                    <PageLink href={page.prev_page_url} label="Pagina precedente">
                        <ChevronLeft className="size-4" aria-hidden />
                    </PageLink>
                    <div className="hidden items-center gap-1 sm:flex">
                        {numbered.map((link, index) =>
                            link.url === null ? (
                                <span key={`gap-${index}`} className="px-2 text-sm text-fg-subtle">
                                    …
                                </span>
                            ) : (
                                <Link
                                    key={link.url}
                                    href={link.url}
                                    preserveScroll
                                    aria-current={link.active ? 'page' : undefined}
                                    className={cn(
                                        'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-medium transition-colors',
                                        link.active ? 'bg-brand-600 text-white' : 'text-fg-muted hover:bg-brand-50 hover:text-brand-800',
                                    )}
                                >
                                    {link.label}
                                </Link>
                            ),
                        )}
                    </div>
                    <span className="px-2 text-sm text-fg-muted sm:hidden">
                        {page.current_page} / {page.last_page}
                    </span>
                    <PageLink href={page.next_page_url} label="Pagina successiva">
                        <ChevronRight className="size-4" aria-hidden />
                    </PageLink>
                </div>
            )}
        </nav>
    );
}

function PageLink({ href, label, children }: { href: string | null; label: string; children: React.ReactNode }) {
    const classes = 'inline-flex size-9 items-center justify-center rounded-lg border border-line text-fg-muted';
    if (!href) {
        return (
            <span className={cn(classes, 'opacity-40')} aria-disabled="true" aria-label={label}>
                {children}
            </span>
        );
    }
    return (
        <Link href={href} preserveScroll className={cn(classes, 'hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800')} aria-label={label}>
            {children}
        </Link>
    );
}
