import '../../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

type PageModules = Record<string, () => Promise<unknown>>;

/**
 * Boots the Inertia app of one area. Each area has its own bundle, its own
 * set of pages and its own XSRF cookie name.
 */
export function startArea({ pages, xsrfCookie }: { pages: PageModules; xsrfCookie: string }): void {
    const nonce = document.querySelector<HTMLMetaElement>('meta[name="csp-nonce"]')?.content;

    void createInertiaApp({
        title: (title) => title,
        resolve: async (name) => {
            const importer = pages[`./pages/${name}.tsx`];
            if (!importer) throw new Error(`Pagina non disponibile: ${name}`);
            const module = (await importer()) as { default: ComponentType };
            return module.default;
        },
        setup({ el, App, props }) {
            createRoot(el).render(<App {...props} />);
        },
        progress: { color: '#2a6fff', delay: 180 },
        http: { xsrfCookieName: xsrfCookie },
        ...(nonce ? { nonce } : {}),
    });
}
