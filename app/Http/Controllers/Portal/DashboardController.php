<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Settings\SettingsRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Customer dashboard. Only data of the authenticated account is shown.
 *
 * Catalogue, cart, orders and company customer data depend on the analysis of
 * the company SQL Server database: they are NOT simulated here; the page says
 * so explicitly.
 */
final class DashboardController extends Controller
{
    public function __invoke(Request $request, SettingsRepository $settings): Response
    {
        /** @var PortalAccount $account */
        $account = $request->user();

        return Inertia::render('Portal/Dashboard', [
            'account' => [
                'name' => $account->display_name,
                'email' => $account->email,
                'username' => $account->username,
                'customerRef' => $account->customer_ref,
                'lastLoginAt' => $account->last_login_at?->toIso8601String(),
            ],
            'pickup' => $settings->get('pickup'),
            'company' => [
                'phone' => $settings->value('company', 'phone'),
                'email' => $settings->value('company', 'email'),
                'officeHours' => $settings->value('company', 'office_hours'),
            ],
        ]);
    }
}
