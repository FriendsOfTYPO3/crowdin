<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Xclass;

use FriendsOfTYPO3\Crowdin\UserConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageServiceXclassed extends LanguageService
{
    /** @var UserConfiguration */
    protected $userConfiguration;

    public const CORE_EXTENSIONS = [
        'about',
        'adminpanel',
        'backend',
        'belog',
        'beuser',
        'core',
        'dashboard',
        'extbase',
        'extensionmanager',
        'felogin',
        'filelist',
        'filemetadata',
        'fluid',
        'fluid_styled_content',
        'form',
        'frontend',
        'impexp',
        'indexed_search',
        'info',
        'install',
        'linkvalidator',
        'lowlevel',
        'opendocs',
        'reactions',
        'recordlist',
        'recycler',
        'redirects',
        'reports',
        'rte_ckeditor',
        'scheduler',
        'seo',
        'setup',
        'styleguide',
        'sys_note',
        't3editor',
        'tstemplate',
        'viewpage',
        'webhooks',
        'workspaces',
    ];

    public function sL($input): string
    {
        $this->reinitLanguage($input);

        return parent::sL($input);
    }

    protected function readLLfile($fileRef): array
    {
        $this->reinitLanguage($fileRef);

        return parent::readLLfile($fileRef);
    }

    protected function reinitLanguage($path): void
    {
        if (!is_string($path)) {
            return;
        }
        $typo3Version = (new Typo3Version())->getMajorVersion();

        $this->loadUserConfiguration();
        if ($this->userConfiguration->usedForCore) {
            $isCoreExt = false;
            foreach (self::CORE_EXTENSIONS as $extension) {
                if (str_contains($path, 'EXT:' . $extension)) {
                    $isCoreExt = true;
                }
            }
            if ($isCoreExt) {
                $this->lang = 't3';
            } else {
                $this->lang = $typo3Version >= 14 ? 'en' : 'default';
            }
        } elseif ($this->userConfiguration->crowdinIdentifier) {
            $useT3 = str_contains($path, 'EXT:' . $this->userConfiguration->extensionKey);
            if (!$useT3 && $typo3Version >= 14) {
                // TYPO3 v14 is using a <domain>.<key> syntax as well (e.g., in Frontend)
                $useT3 = str_starts_with($path, $this->userConfiguration->extensionKey . '.');
            }
            if ($useT3) {
                $this->lang = 't3';
            } else {
                $this->lang = $typo3Version >= 14 ? 'en' : 'default';
            }
        }
    }

    protected function loadUserConfiguration(): void
    {
        if (!$this->userConfiguration) {
            $this->userConfiguration = GeneralUtility::makeInstance(UserConfiguration::class);
        }
    }
}
