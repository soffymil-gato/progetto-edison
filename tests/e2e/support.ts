import AxeBuilder from '@axe-core/playwright';
import { expect, type Page } from '@playwright/test';

export const PASSWORD = process.env.E2E_PASSWORD ?? 'E2e-password-locale-1';

export const accounts = {
    admin: 'e2e.admin@example.test',
    operator: 'e2e.operatore@example.test',
    customerUsername: 'cliente.e2e',
};

/** Fails the test on console errors, page errors and CSP violations. */
export function trackErrors(page: Page): string[] {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') errors.push(message.text());
    });
    page.on('pageerror', (error) => errors.push(error.message));
    return errors;
}

export async function expectNoHorizontalScroll(page: Page): Promise<void> {
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(overflow, 'horizontal scroll').toBeLessThanOrEqual(1);
}

/** WCAG 2.2 AA automated checks: no serious or critical violation. */
export async function expectAccessible(page: Page): Promise<void> {
    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    const severe = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    const details = severe.flatMap((v) =>
        v.nodes.map(
            (n) =>
                `${v.id}: ${n.target.join(' ')} — ${n.failureSummary?.split('\n').slice(1, 2).join(' ').trim() ?? ''}`,
        ),
    );
    expect(details).toEqual([]);
}

export async function login(
    page: Page,
    area: 'gestionale' | 'area-clienti',
    identifier: string,
    password = PASSWORD,
    expectSuccess = true,
): Promise<void> {
    await page.goto(`/${area}/login`);
    await page.locator('input[autocomplete="username"]').fill(identifier);
    await page.locator('input[autocomplete="current-password"]').fill(password);
    await page.getByRole('button', { name: 'Accedi' }).click();
    if (expectSuccess) {
        await page.waitForURL((url) => url.pathname === `/${area}`);
    }
}
