import { expect, test } from '@playwright/test';

import { accounts, expectAccessible, expectNoHorizontalScroll, login, trackErrors } from './support';

test('login del personale, panoramica e uscita', async ({ page }) => {
    const errors = trackErrors(page);
    await login(page, 'gestionale', accounts.admin);
    await expect(page).toHaveURL(/\/gestionale$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Admin');
    await expect(page.getByText('Prontezza al rilascio')).toBeVisible();
    await expectNoHorizontalScroll(page);
    await expectAccessible(page);

    await page.getByRole('button', { name: 'Menu utente' }).click();
    await page.getByRole('menuitem', { name: /Esci/ }).click();
    await expect(page).toHaveURL(/\/gestionale\/login$/);
    expect(errors).toEqual([]);
});

test('credenziali errate: messaggio generico, nessun accesso', async ({ page }) => {
    await login(page, 'gestionale', accounts.admin, 'password-sbagliata-123', false);
    await expect(page.getByText('Credenziali non valide. Controlla i dati inseriti e riprova.')).toBeVisible();
    await expect(page).toHaveURL(/\/gestionale\/login$/);
});

test('creazione di un utente interno con invito e ricerca', async ({ page, isMobile }) => {
    await login(page, 'gestionale', accounts.admin);
    await page.goto('/gestionale/utenti/nuovo');
    const email = `nuovo.${isMobile ? 'm' : 'd'}.${Date.now()}@example.test`;
    await page.getByLabel(/Nome e cognome/).fill('Nuovo Collega');
    await page.getByLabel(/Email aziendale/).fill(email);
    await page.getByText('Operatore', { exact: true }).click();
    await page.getByRole('button', { name: 'Crea e invia invito' }).click();
    await expect(page.getByText("Utente creato. È stato inviato l'invito per attivare l'account.")).toBeVisible();

    await page.goto('/gestionale/utenti');
    await page.getByRole('searchbox', { name: 'Cerca per nome o email' }).fill(email);
    await expect(page.getByText(email).filter({ visible: true })).toBeVisible();
    await expect(page.getByText('Invitato', { exact: true }).filter({ visible: true }).first()).toBeVisible();
    await expectAccessible(page);
});

test('un operatore senza permessi non accede alle funzioni riservate', async ({ page }) => {
    await login(page, 'gestionale', accounts.operator);
    await expect(page).toHaveURL(/\/gestionale$/);
    await expect(page.getByRole('link', { name: 'Utenti interni' })).toHaveCount(0);

    const response = await page.goto('/gestionale/utenti');
    expect(response?.status()).toBe(403);
    await expect(page.getByRole('heading', { name: 'Accesso non consentito' })).toBeVisible();
});

test('impostazioni aziendali: salvataggio e pubblicazione sul sito', async ({ page }, testInfo) => {
    const value = `Dati di prova E2E (${testInfo.project.name}) — non reali`;
    await login(page, 'gestionale', accounts.admin);
    await page.goto('/gestionale/impostazioni');
    const card = page.locator('#pickup');
    await card.getByLabel(/Orari di ritiro/).fill(value);
    await card.getByRole('button', { name: 'Salva' }).click();
    await expect(page.getByText('Impostazioni salvate.')).toBeVisible();

    await page.goto('/servizi');
    await expect(page.getByText(value)).toBeVisible();
});
