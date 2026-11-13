import { defineConfig, devices } from '@playwright/test';

/**
 * End-to-end tests against a real Laravel server and a dedicated SQL Server
 * database (EdisonPortal_e2e, reset by the global setup). Never production.
 */
const port = Number(process.env.E2E_PORT ?? 8010);
export const e2eEnv = {
    APP_ENV: 'e2e',
    APP_URL: `http://localhost:${port}`,
    DB_DATABASE: process.env.E2E_DB_DATABASE ?? 'EdisonPortal_e2e',
    SESSION_SECURE_COOKIE: 'false',
    MAIL_MAILER: 'array',
    QUEUE_CONNECTION: 'sync',
    CACHE_STORE: 'array',
};

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
    globalSetup: './tests/e2e/global-setup.ts',
    use: {
        baseURL: `http://localhost:${port}`,
        trace: 'off',
        locale: 'it-IT',
        timezoneId: 'Europe/Rome',
        // The site honours prefers-reduced-motion: checks run on the settled layout.
        reducedMotion: 'reduce',
    },
    projects: [
        { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } },
        { name: 'mobile', use: { ...devices['Pixel 7'] } },
    ],
    webServer: {
        command: `php artisan serve --host=localhost --port=${port}`,
        url: `http://localhost:${port}/up`,
        reuseExistingServer: false,
        timeout: 60_000,
        env: e2eEnv,
    },
});
