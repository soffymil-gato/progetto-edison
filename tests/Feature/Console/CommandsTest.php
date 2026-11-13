<?php

declare(strict_types=1);

use App\Domain\Content\ReleaseReadiness;
use App\Domain\Content\SiteContent;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Settings\SettingsRepository;
use App\Domain\Settings\SettingsSchema;
use Illuminate\Support\Facades\Notification;

it('blocks the release while placeholders or company data are missing', function (): void {
    $this->artisan('edison:release-check')
        ->expectsOutputToContain('Testi del sito forniti da Edison')
        ->assertFailed();
});

it('passes the release check when content, settings and logo are complete', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'content').'.php';
    file_put_contents($path, '<?php return ["home" => ["title" => "Testo definitivo"]];');
    app()->instance(SiteContent::class, new SiteContent($path));

    $logo = 'brand/test-logo.svg';
    @mkdir(public_path('brand'));
    file_put_contents(public_path($logo), '<svg xmlns="http://www.w3.org/2000/svg"/>');
    config(['edison.brand.logo_path' => $logo]);

    $admin = $this->superAdmin();
    foreach (SettingsSchema::groups() as $group => $definition) {
        $values = [];
        foreach ($definition['fields'] as $field => $meta) {
            $values[$field] = $meta['type'] === 'email' ? "{$field}@example.test" : "Valore {$field}";
        }
        app(SettingsRepository::class)->update($group, $values, $admin);
    }

    try {
        expect(app(ReleaseReadiness::class)->isReady())->toBeTrue();
        $this->artisan('edison:release-check')->assertSuccessful();
    } finally {
        @unlink(public_path($logo));
        @rmdir(public_path('brand'));
        @unlink($path);
    }
});

it('creates the first Super Admin as invited, without any password typed in the console', function (): void {
    Notification::fake();

    $this->artisan('edison:create-super-admin', ['--name' => 'Prima Persona', '--email' => 'prima@example.test'])->assertSuccessful();

    $user = StaffUser::query()->where('email', 'prima@example.test')->sole();
    expect($user->isSuperAdmin())->toBeTrue()->and($user->status)->toBe(AccountStatus::Invited)->and($user->password)->toBeNull();
});

it('refuses to create customer accounts from the console in production', function (): void {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('edison:portal-invite', ['email' => 'c@example.test', '--name' => 'C', '--customer-ref' => 'X'])->assertFailed();
    expect(PortalAccount::query()->count())->toBe(0);
});
