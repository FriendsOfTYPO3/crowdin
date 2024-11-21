<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Crowdin',
    'description' => 'In-Context localization of XLF files handled by Crowdin directly in the backend',
    'category' => 'be',
    'author' => 'TYPO3 Localization Team',
    'author_email' => 'friendsof@typo3.org',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '11.5.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
