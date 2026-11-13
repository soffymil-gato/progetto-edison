<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Content\ReleaseReadiness;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Release gate: fails while any placeholder, missing company data or the
 * official logo would reach production. Run it in the deployment pipeline.
 */
#[Signature('edison:release-check')]
#[Description('Verifica che non restino contenuti segnaposto o dati aziendali mancanti prima del rilascio.')]
final class ReleaseCheckCommand extends Command
{
    public function handle(ReleaseReadiness $readiness): int
    {
        $ready = true;

        foreach ($readiness->checks() as $check) {
            if ($check['ok']) {
                $this->line("<info>✔</info> {$check['label']}");

                continue;
            }

            $ready = false;
            $this->line("<error>✘</error> {$check['label']} — mancanti: ".count($check['missing']));
            foreach ($check['missing'] as $missing) {
                $this->line("    · {$missing}");
            }
        }

        if (! $ready) {
            $this->newLine();
            $this->error('Rilascio bloccato: completare i contenuti e i dati indicati.');

            return self::FAILURE;
        }

        $this->info('Nessun segnaposto: contenuti pronti per il rilascio.');

        return self::SUCCESS;
    }
}
