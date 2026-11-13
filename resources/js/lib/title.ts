import type { AreaName } from './types';

const suffix: Record<AreaName, string> = { public: '', portal: 'Area clienti', admin: 'Gestionale' };

export function pageTitle(title: string, area: AreaName, appName: string): string {
    const parts = [title, suffix[area], appName].filter(Boolean);
    return parts.join(' — ');
}
