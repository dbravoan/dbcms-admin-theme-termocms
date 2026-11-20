<?php

declare(strict_types=1);

use DbCms\Core\Contracts\Services\AdminThemeServiceInterface;
use DbCms\Core\Models\User;
use Tests\Support\BlogFixtures;
use Tests\Support\CmsFixtures;

it('renders its optional Blog dashboard presentation from its owned override', function (): void {
    $this->withoutVite();
    CmsFixtures::localization();
    CmsFixtures::identities();
    BlogFixtures::post();
    app(AdminThemeServiceInterface::class)->setActiveTheme('termocms');
    $admin = User::where('email', 'admin@db-cms.io')->sole();
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('termoCMS');
    $view = view()->getFinder()->find('blog::admin.dashboard.metrics');
    expect(realpath($view))->toBe(realpath(dirname(__DIR__, 2).'/modules/blog/admin/dashboard/metrics.blade.php'));
});
