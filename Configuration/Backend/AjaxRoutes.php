<?php

use FriendsOfTYPO3\Crowdin\Backend\ToolbarItems\CrowdinToolbarItem;

/**
 * Definitions for AJAX routes provided by EXT:crowdin
 */
return [
    'crowdin_toggletranslation' => [
        'path' => '/menu/crowdin/translation/toggle',
        'target' => CrowdinToolbarItem::class . '::toggleTranslationMode',
    ],
    'crowdin_setextension' => [
        'path' => 'menu/crowdin/extension/set',
        'target' => CrowdinToolbarItem::class . '::setCurrentExtension',
    ],
];
