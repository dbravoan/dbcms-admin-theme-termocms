<?php

declare(strict_types=1);

use DbCms\Core\Contracts\Services\AdminThemeServiceInterface;
use DbCms\Core\Models\User;
use Tests\Support\CmsFixtures;

test('the optional admin retains shared login and dashboard controls alongside the native brand', function (string $theme, string $brand): void {
    CmsFixtures::localization();
    CmsFixtures::identities();
    app(AdminThemeServiceInterface::class)->setActiveTheme($theme);
    $formsView = view('forms::admin.dashboard.actions')->getPath();
    if ($theme === 'termocms') {
        expect($formsView)->toStartWith(dirname(__DIR__, 2).'/modules/forms/');
    } else {
        expect($formsView)->not->toStartWith(dirname(__DIR__, 2).'/modules/forms/');
    }

    $login = $this->get(route('admin.login'))->assertOk()->assertSee($brand)->assertSee('id="login-email"', false)->assertSee('id="login-password"', false);
    $admin = User::where('email', 'admin@db-cms.io')->sole();
    $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('data-admin-shell', false);

    $directory = getenv('DBCMS_ADMIN_FIXTURE_DIR');
    if (is_string($directory) && $directory !== '') {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($directory.'/'.$theme.'-login.html', $login->getContent());
        file_put_contents($directory.'/'.$theme.'-dashboard.html', $dashboard->getContent());
    }
})->with([['dbcms', 'dbCMS'], ['termocms', 'Termosalud']]);
