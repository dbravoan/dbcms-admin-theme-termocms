<?php

declare(strict_types=1);

use DbCms\Core\Contracts\Services\AdminThemeServiceInterface;
use DbCms\Core\Contracts\Services\BrandServiceInterface;
use DbCms\Core\Models\User;
use DbCms\Core\Services\Install\BrandRegistry;
use Tests\Support\CmsFixtures;

beforeEach(function (): void {
    $this->withoutVite();
    CmsFixtures::localization();
    CmsFixtures::identities();
    app(AdminThemeServiceInterface::class)->setActiveTheme('termocms');
});

it('discovers Termosalud identity and its public logo assets', function (): void {
    $registry = app(BrandRegistry::class);
    expect($registry->brandIdentifiers())->toContain('termocms');
    $brand = $registry->find('termocms');
    expect($brand['name'])->toBe('Termosalud')
        ->and($brand['accent'])->toBe('#0090A8')
        ->and($brand['mode'])->toBe('light')
        ->and($brand['edition'])->toBeNull()
        ->and($brand['logo'])->toContain('termosalud-logo.png');
    $this->get($brand['logo'])->assertOk();
    $this->get($brand['icon'])->assertOk();
});

it('resolves the active theme brand without a second identity source', function (): void {
    $brands = app(BrandServiceInterface::class);
    $brand = $brands->current();
    expect($brands->activeTheme())->toBe('termocms')
        ->and($brand->theme)->toBe('termocms')
        ->and($brand->name)->toBe('Termosalud')
        ->and($brand->tagline)->toBe('Plataforma Corporativa')
        ->and($brand->edition)->toBeNull()
        ->and($brand->accent)->toBe('#0090A8')
        ->and($brand->mode)->toBe('light')
        ->and($brand->logo)->toBe('termosalud-logo.png')
        ->and($brand->valid)->toBeTrue()
        ->and($brands->forTheme('termocms')->name)->toBe('Termosalud');
});

it('renders the Termosalud login branding with accessible shared controls', function (): void {
    $this->get(route('admin.login'))->assertOk()->assertSee('Termosalud')->assertSee('Autenticación Segura');
    $html = view('admin.auth.login', ['cspNonce' => 'test-nonce'])->render();
    expect($html)->toContain('id="login-heading"', __('admin.auth.login_subtitle'), 'autocomplete="username"', 'autocomplete="current-password"', 'data-password-toggle="login-password"', 'aria-pressed', 'fa-envelope');
});

it('renders its dashboard and matching error presentation without optional modules', function (): void {
    $admin = User::where('email', 'admin@db-cms.io')->sole();
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('termoCMS')->assertDontSee('CORPORATE');
    $this->get('/a-completely-non-existent-cms-route-404')->assertNotFound()->assertSee('Termosalud');
});

it('renders the configured version unchanged in the real Termosalud dashboard', function (string $version): void {
    expect(realpath(view('admin.dashboard')->getPath()))
        ->toBe(realpath(dirname(__DIR__, 2).'/admin/dashboard.blade.php'));
    config(['cms.version' => $version]);
    $admin = User::where('email', 'admin@db-cms.io')->sole();
    $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $badge = e(__('admin.dashboard.platform_corporate')).' &bull; ';

    $response->assertSee($badge.$version, false)
        ->assertDontSee($badge.'v'.$version, false);
    expect(config('cms.version'))->toBe($version);
})->with(['plain version' => ['0.1.2'], 'prefixed version' => ['v0.1.2']]);
