<?php

declare(strict_types=1);

return [
    'laravel' => ['version' => '13.11.0', 'reference' => '06d016a364a37430eec9cbc52209adce3d7667ce'],
    'kind' => 'admin-theme',
    'core' => '0.0.1',
    'fixtureVersion' => '0.0.1',
    'theme' => 'default-clean',
    'adminTheme' => 'termocms',
    'require' => [],
    'sourceDirs' => [],
    'testPaths' => ['Architecture', 'Feature'],
    'applicationTestPaths' => ['Feature', 'Integration'],
    'modules' => [],
    'blockPackages' => [],
    'blockTypes' => [],
    'privacyProcessing' => [],
    'preserveRows' => [],
    'profiles' => [
        'blog' => [
            'require' => ['dbravoan/dbcms-module-blog' => '0.0.1'],
            'modules' => ['blog'],
            'testPaths' => ['Integration/BlogThemeTest.php'],
        ],
    ],
];
