<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'crowdin-toolbar-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:crowdin/Resources/Public/Icons/toolbar-icon.svg',
    ],
];
