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
    test(`pagina ${path}: contenuto, accessibilità e layout`, async ({ page, isMobile }) => {
        const errors = trackErrors(page);
        await page.goto(path);
        await expect(page.getByRole('heading', { level: 1, name: heading })).toBeVisible();

        const headerLink = page.getByRole('banner').getByRole('link', { name: 'Area clienti' });
        if (isMobile) {
            // On phones the header button is hidden: the entry point is in the menu.
            await expect(headerLink).toBeHidden();
            await page.getByRole('button', { name: 'Apri il menu' }).click();
            await expect(page.getByRole('dialog').getByRole('link', { name: 'Area clienti' })).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(page.getByRole('dialog')).toBeHidden();
        } else {
            await expect(headerLink).toBeVisible();
        }

        await expectNoHorizontalScroll(page);
        await expectAccessible(page);
        expect(errors).toEqual([]);
    });
}

// The header is fixed, so overflow there does not show up as horizontal scroll.
test("l'intestazione sta nello schermo anche sui telefoni stretti", async ({ page }) => {
    for (const width of [320, 360, 412]) {
        await test.step(`${width}px`, async () => {
            await page.setViewportSize({ width, height: 800 });
            await page.goto('/');
            const menuButton = page.getByRole('banner').getByRole('button', { name: 'Apri il menu' });
            await expect(menuButton).toBeVisible();
            const outside = await page
                .getByRole('banner')
                .locator('a, button, img, [role="img"]')
                .evaluateAll((elements) =>
                    elements
                        .filter((el) => el.checkVisibility())
                        .map((el) => ({ el, box: el.getBoundingClientRect() }))
                        .filter(({ box }) => box.left < 0 || box.right > window.innerWidth)
                        .map(
                            ({ el, box }) =>
                                `${el.getAttribute('aria-label') ?? el.textContent}: ${box.left}–${box.right}`,
                        ),
                );
            expect(outside).toEqual([]);
        });
    }
});

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
