<?php

declare(strict_types=1);

it('declares legible independent admin bar alert surfaces', function (): void {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/admin-bar.css');
    foreach (['--admin-bar-alert-warning-bg', '--admin-bar-alert-warning-border', '--admin-bar-alert-warning-text', '--admin-bar-alert-success-bg', '--admin-bar-alert-success-text', '--admin-bar-alert-danger-bg', '--admin-bar-alert-danger-text'] as $token) {
        expect($css)->toContain($token);
    }
});

it('ships the declared corporate logo and icon as local assets', function (): void {
    foreach (['termosalud-logo.png', 'termosalud-favicon.png'] as $asset) {
        $image = getimagesize(dirname(__DIR__, 2).'/assets/'.$asset);
        expect($image)->not->toBeFalse()->and($image[0])->toBeGreaterThan(0)->and($image[1])->toBeGreaterThan(0);
    }
});
