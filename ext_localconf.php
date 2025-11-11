<?php

defined('TYPO3') or die();

$boot = static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']['crowdin-inline-translation']
        = \FriendsOfTYPO3\Crowdin\Hooks\PageRendererHook::class . '->run';

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Localization\LanguageService::class] = [
        'className' => \FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceXclassed::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Localization\LanguageServiceFactory::class] = [
        'className' => \FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceFactoryXclassed::class,
    ];
};

$boot();
unset($boot);
