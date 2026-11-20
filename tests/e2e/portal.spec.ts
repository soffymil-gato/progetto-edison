import { expect, test } from '@playwright/test';

import { accounts, expectAccessible, expectNoHorizontalScroll, login, trackErrors } from './support';

test('login del cliente con nome utente e dashboard personale', async ({ page }) => {
    const errors = trackErrors(page);
    await login(page, 'area-clienti', accounts.customerUsername);
    await expect(page).toHaveURL(/\/area-clienti$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Ciao, Cliente' })).toBeVisible();
    await expect(page.getByText('TEST-E2E-001')).toBeVisible();
    await expect(page.getByText('Catalogo e ordini in attivazione')).toBeVisible();
    await expectNoHorizontalScroll(page);
    await expectAccessible(page);
    expect(errors).toEqual([]);
});

test('un cliente autenticato non accede al gestionale', async ({ page }) => {
    await login(page, 'area-clienti', accounts.customerUsername);
    await expect(page).toHaveURL(/\/area-clienti$/);

    await page.goto('/gestionale');
    await expect(page).toHaveURL(/\/gestionale\/login$/);
});

test('recupero password: risposta identica per indirizzi esistenti e non', async ({ page }) => {
    for (const email of ['e2e.cliente@example.test', 'nessuno@example.test']) {
        await page.goto('/area-clienti/password/recupero');
        await page.getByLabel(/Email/).fill(email);
        await page.getByRole('button', { name: 'Invia il link' }).click();
        await expect(page.getByText("Se l'indirizzo corrisponde a un account attivo")).toBeVisible();
    }
});
