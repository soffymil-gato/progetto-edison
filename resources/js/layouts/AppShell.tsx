import { Head, Link, router } from '@inertiajs/react';
import { ChevronDown, ExternalLink, LogOut, Menu, UserCog, X } from 'lucide-react';
import { Dialog, DropdownMenu } from 'radix-ui';
import { type ReactNode, useState } from 'react';

import { BrandMark } from '@/design-system/BrandMark';
import { NavIcon } from '@/design-system/Icon';
import { Toaster } from '@/design-system/Toaster';
import { cn } from '@/lib/cn';
import { pageTitle } from '@/lib/title';
import type { NavItem } from '@/lib/types';
import { routeMatches, useShared } from '@/lib/useShared';

/**
 * Application shell of the reserved areas.
 * - Admin panel: dark sidebar for dense daily work.
 * - Customer area: light horizontal navigation, closer to the public website.
 */
export function AppShell({ title, children }: { title: string; children: ReactNode }) {
    const { area, appName } = useShared();

    return (
        <>
            <Head title={pageTitle(title, area, appName)} />
            {area === 'admin' ? <AdminFrame>{children}</AdminFrame> : <PortalFrame>{children}</PortalFrame>}
            <Toaster />
        </>
    );
}

function AdminFrame({ children }: { children: ReactNode }) {
    const { nav, route } = useShared();
    const [open, setOpen] = useState(false);

    return (
        <div className="min-h-dvh bg-canvas lg:grid lg:grid-cols-[16.5rem_1fr]">
            <aside className="sticky top-0 hidden h-dvh flex-col bg-brand-950 lg:flex">
                <SidebarContent items={nav} route={route} />
            </aside>

            <Dialog.Root open={open} onOpenChange={setOpen}>
                <Dialog.Portal>
                    <Dialog.Overlay className="fixed inset-0 z-50 animate-fade-in bg-ink/50 backdrop-blur-sm lg:hidden" />
                    <Dialog.Content className="fixed inset-y-0 left-0 z-50 flex w-[min(18rem,85%)] animate-fade-in flex-col bg-brand-950 shadow-elevated lg:hidden">
                        <Dialog.Title className="sr-only">Menu del gestionale</Dialog.Title>
                        <Dialog.Close
                            className="absolute top-4 right-3 inline-flex size-10 items-center justify-center rounded-lg text-white/70 hover:bg-white/10"
                            aria-label="Chiudi il menu"
                        >
                            <X className="size-5" aria-hidden />
                        </Dialog.Close>
                        <SidebarContent items={nav} route={route} onNavigate={() => setOpen(false)} />
                    </Dialog.Content>
                </Dialog.Portal>
            </Dialog.Root>

            <div className="flex min-w-0 flex-col">
                <header className="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-surface/90 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
                    <button
                        type="button"
                        onClick={() => setOpen(true)}
                        className="inline-flex size-10 items-center justify-center rounded-lg text-fg hover:bg-brand-50 lg:hidden"
                        aria-label="Apri il menu"
                    >
                        <Menu className="size-5" aria-hidden />
                    </button>
                    <div className="lg:hidden">
                        <BrandMark />
                    </div>
                    <div className="ml-auto">
                        <UserMenu />
                    </div>
                </header>
                <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">{children}</main>
            </div>
        </div>
    );
}

function SidebarContent({
    items,
    route,
    onNavigate,
}: {
    items: NavItem[];
    route: string | null;
    onNavigate?: () => void;
}) {
    const { urls } = useShared();
    return (
        <>
            <div className="flex h-16 items-center px-5">
                <BrandMark tone="light" />
            </div>
            <p className="px-5 pt-2 pb-3 text-[0.6875rem] font-semibold tracking-[0.18em] text-white/60 uppercase">
                Gestionale
            </p>
            <nav aria-label="Menu del gestionale" className="flex-1 overflow-y-auto px-3">
                <ul className="flex flex-col gap-0.5">
                    {items.map((item) => {
                        const active = routeMatches(route, item.match);
                        return (
                            <li key={item.href}>
                                <Link
                                    href={item.href}
                                    onClick={onNavigate}
                                    aria-current={active ? 'page' : undefined}
                                    className={cn(
                                        'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                                        active
                                            ? 'bg-white/10 text-white'
                                            : 'text-white/65 hover:bg-white/5 hover:text-white',
                                    )}
                                >
                                    {active && (
                                        <span
                                            className="absolute top-2 bottom-2 -left-3 w-1 rounded-r-full bg-spark-400"
                                            aria-hidden
                                        />
                                    )}
                                    <NavIcon
                                        name={item.icon}
                                        className={cn(
                                            'size-[1.125rem]',
                                            active ? 'text-spark-300' : 'text-white/45 group-hover:text-white/80',
                                        )}
                                    />
                                    {item.label}
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            </nav>
            <div className="border-t border-white/10 p-3">
                <a
                    href={urls.publicHome}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-white/55 hover:bg-white/5 hover:text-white"
                >
                    <ExternalLink className="size-4" aria-hidden />
                    Vai al sito pubblico
                </a>
            </div>
        </>
    );
}

function PortalFrame({ children }: { children: ReactNode }) {
    const { nav, route, urls } = useShared();
    const [open, setOpen] = useState(false);

    return (
        <div className="flex min-h-dvh flex-col bg-canvas">
            <header className="sticky top-0 z-30 border-b border-line bg-surface/90 backdrop-blur-xl">
                <div className="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6 lg:px-8">
                    <Link href={urls.home ?? '#'} aria-label="Dashboard">
                        <BrandMark />
                    </Link>
                    <nav aria-label="Menu dell'area clienti" className="hidden md:block">
                        <ul className="flex items-center gap-1">
                            {nav.map((item) => {
                                const active = routeMatches(route, item.match);
                                return (
                                    <li key={item.href}>
                                        <Link
                                            href={item.href}
                                            aria-current={active ? 'page' : undefined}
                                            className={cn(
                                                'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition-colors',
                                                active
                                                    ? 'bg-brand-50 text-brand-800 ring-1 ring-brand-100'
                                                    : 'text-fg-muted hover:bg-canvas hover:text-fg',
                                            )}
                                        >
                                            <NavIcon name={item.icon} className="size-4" />
                                            {item.label}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </nav>
                    <div className="ml-auto flex items-center gap-1">
                        <UserMenu />
                        <button
                            type="button"
                            onClick={() => setOpen((v) => !v)}
                            className="inline-flex size-10 items-center justify-center rounded-lg hover:bg-brand-50 md:hidden"
                            aria-expanded={open}
                            aria-controls="portal-mobile-nav"
                            aria-label={open ? 'Chiudi il menu' : 'Apri il menu'}
                        >
                            {open ? <X className="size-5" aria-hidden /> : <Menu className="size-5" aria-hidden />}
                        </button>
                    </div>
                </div>
                {open && (
                    <nav
                        id="portal-mobile-nav"
                        aria-label="Menu dell'area clienti"
                        className="border-t border-line px-4 py-3 md:hidden"
                    >
                        <ul className="flex flex-col gap-1">
                            {nav.map((item) => (
                                <li key={item.href}>
                                    <Link
                                        href={item.href}
                                        onClick={() => setOpen(false)}
                                        aria-current={routeMatches(route, item.match) ? 'page' : undefined}
                                        className={cn(
                                            'flex items-center gap-3 rounded-xl px-3 py-3 text-[0.9375rem] font-medium',
                                            routeMatches(route, item.match)
                                                ? 'bg-brand-50 text-brand-800'
                                                : 'text-fg hover:bg-canvas',
                                        )}
                                    >
                                        <NavIcon name={item.icon} className="size-4.5" />
                                        {item.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                )}
            </header>
            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-10 lg:px-8">{children}</main>
        </div>
    );
}

function UserMenu() {
    const { auth, urls, area } = useShared();
    const user = auth.user;
    if (!user) return null;

    const initials = user.name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
    const roles = 'roles' in user ? user.roles.join(', ') : null;

    return (
        <DropdownMenu.Root>
            <DropdownMenu.Trigger className="inline-flex items-center gap-2.5 rounded-full py-1 pr-2 pl-1 transition-colors hover:bg-brand-50 data-[state=open]:bg-brand-50">
                <span
                    className="inline-flex size-8 items-center justify-center rounded-full bg-gradient-to-br from-brand-600 to-brand-800 text-xs font-semibold text-white"
                    aria-hidden
                >
                    {initials}
                </span>
                <span className="hidden max-w-40 truncate text-sm font-medium text-fg sm:block">{user.name}</span>
                <ChevronDown className="size-4 text-fg-subtle" aria-hidden />
                <span className="sr-only">Menu utente</span>
            </DropdownMenu.Trigger>
            <DropdownMenu.Portal>
                <DropdownMenu.Content
                    align="end"
                    sideOffset={8}
                    className="z-50 w-64 animate-fade-in rounded-card border border-line bg-surface p-1.5 shadow-elevated"
                >
                    <div className="px-3 py-2.5">
                        <p className="truncate text-sm font-semibold text-fg">{user.name}</p>
                        <p className="truncate text-xs text-fg-muted">{user.email}</p>
                        {roles && <p className="mt-1 truncate text-xs text-brand-700">{roles}</p>}
                    </div>
                    <DropdownMenu.Separator className="my-1 h-px bg-line" />
                    <DropdownMenu.Item asChild>
                        <Link
                            href={urls.profile ?? '#'}
                            className="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-fg outline-none data-[highlighted]:bg-brand-50"
                        >
                            <UserCog className="size-4 text-fg-subtle" aria-hidden />
                            Profilo e sicurezza
                        </Link>
                    </DropdownMenu.Item>
                    <DropdownMenu.Item
                        onSelect={() => router.post(urls.logout ?? '#')}
                        className="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-danger-700 outline-none data-[highlighted]:bg-danger-50"
                    >
                        <LogOut className="size-4" aria-hidden />
                        Esci {area === 'admin' ? 'dal gestionale' : "dall'area clienti"}
                    </DropdownMenu.Item>
                </DropdownMenu.Content>
            </DropdownMenu.Portal>
        </DropdownMenu.Root>
    );
}
