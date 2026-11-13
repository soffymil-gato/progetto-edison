import { expect, test } from '@playwright/test';

import { expectAccessible, expectNoHorizontalScroll, trackErrors } from './support';

const pages = [
    { path: '/', heading: 'Prodotti e servizi per il settore energetico' },
    { path: '/azienda', heading: 'Azienda' },
    { path: '/prodotti', heading: 'Prodotti' },
    { path: '/servizi', heading: 'Servizi' },
    { path: '/contatti', heading: 'Contatti' },
    { path: '/privacy', heading: 'Privacy Policy' },
    { path: '/cookie', heading: 'Cookie Policy' },
];

for (const { path, heading } of pages) {
    test(`pagina ${path}: contenuto, accessibilità e layout`, async ({ page }) => {
        const errors = trackErrors(page);
        await page.goto(path);
        await expect(page.getByRole('heading', { level: 1, name: heading })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Area clienti' }).first()).toBeVisible();
        await expectNoHorizontalScroll(page);
        await expectAccessible(page);
        expect(errors).toEqual([]);
    });
}

test('i contenuti mancanti sono marcati in modo visibile', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.placeholder-text').first()).toBeVisible();
    await expect(page.locator('.placeholder-text').first()).toContainText('Da fornire');
});

test('la navigazione principale funziona anche da smartphone', async ({ page, isMobile }) => {
    await page.goto('/');
    if (isMobile) {
        await page.getByRole('button', { name: 'Apri il menu' }).click();
        await page
            .getByRole('dialog')
            .getByRole('link', { name: /Servizi/ })
            .click();
    } else {
        await page
            .getByRole('navigation', { name: 'Navigazione principale' })
            .getByRole('link', { name: 'Servizi' })
            .click();
    }
    await expect(page).toHaveURL(/\/servizi$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Servizi' })).toBeVisible();
});

test('modulo contatti: validazione lato server e invio', async ({ page }) => {
    const errors = trackErrors(page);
    await page.goto('/contatti');
    await page.getByRole('button', { name: 'Invia la richiesta' }).click();
    await expect(page.getByText('Il campo nome e cognome è obbligatorio.')).toBeVisible();

    await page.getByLabel('Nome e cognome').fill('Persona di prova');
    await page.getByLabel('Email').fill('prova.e2e@example.test');
    await page.getByLabel('Oggetto').fill('Richiesta di prova E2E');
    await page.getByLabel('Messaggio').fill('Messaggio inviato dal test end-to-end.');
    await page.getByRole('checkbox').check();
    await page.getByRole('button', { name: 'Invia la richiesta' }).click();

    await expect(page.getByText('Richiesta inviata')).toBeVisible();
    expect(errors).toEqual([]);
});
