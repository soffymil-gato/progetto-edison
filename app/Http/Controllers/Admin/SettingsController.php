<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Models\StaffUser;
use App\Domain\Settings\SettingsRepository;
use App\Domain\Settings\SettingsSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function edit(): Response
    {
        $groups = [];
        foreach (SettingsSchema::groups() as $key => $group) {
            $fields = [];
            foreach ($group['fields'] as $name => $field) {
                $fields[] = ['name' => $name, ...$field];
            }
            $groups[] = [
                'key' => $key,
                'label' => $group['label'],
                'description' => $group['description'],
                'fields' => $fields,
                'values' => $this->settings->get($key),
                'submitUrl' => route('admin.settings.update', $key),
            ];
        }

        return Inertia::render('Admin/Settings/Edit', ['groups' => $groups]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(SettingsSchema::hasGroup($group), 404);

        $labels = array_map(static fn (array $f): string => mb_strtolower($f['label']), SettingsSchema::groups()[$group]['fields']);
        /** @var array<string, string|null> $data */
        $data = $request->validate(SettingsSchema::validationRules($group), [], $labels);

        /** @var StaffUser $actor */
        $actor = $request->user();
        $changed = $this->settings->update($group, $data, $actor);

        return back()->with('success', $changed === [] ? 'Nessuna modifica da salvare.' : 'Impostazioni salvate.');
    }
}
