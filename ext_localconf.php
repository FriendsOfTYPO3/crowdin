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

    $typo3Version = (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion();
    if ($typo3Version < 12) {
        // TYPO3 v11 code only:
        $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][] = \FriendsOfTYPO3\Crowdin\Backend\ToolbarItems\CrowdinToolbarItem::class;
    }
};

$boot();
unset($boot);
