import { execFileSync } from 'node:child_process';

import { e2eEnv } from '../../playwright.config';

/** Fresh schema and synthetic accounts in the dedicated E2E database. */
export default function globalSetup(): void {
    const env = { ...process.env, ...e2eEnv };
    execFileSync('php', ['artisan', 'migrate:fresh', '--force'], { env, stdio: 'inherit' });
    execFileSync('php', ['artisan', 'db:seed', '--class=E2eSeeder', '--force'], { env, stdio: 'inherit' });
}
