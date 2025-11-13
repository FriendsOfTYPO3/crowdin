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

        // TYPO3 v14 switched from "default" to "en" as default language, which makes more sense
        $defaultLanguageCode = $typo3Version >= 14 ? 'en' : 'default';
        $resetLanguageCode = null;

        $this->loadUserConfiguration();
        if ($this->userConfiguration->usedForCore) {
            $isCoreExt = false;
            $extensionName = null;
            if ($typo3Version >= 14) {
                if (!str_contains($path, ':') && str_contains($path, '.')) {
                    // This looks like a domain string
                    [$extensionName,] = explode('.', $path, 2);
                }
            }
            if ($extensionName !== null) {
                $isCoreExt = in_array($extensionName, self::CORE_EXTENSIONS, true);
            } else {
                foreach (self::CORE_EXTENSIONS as $extension) {
                    if (str_contains($path, 'EXT:' . $extension)) {
                        $isCoreExt = true;
                        break;
                    }
                }
            }
            if ($isCoreExt) {
                $resetLanguageCode = 't3';
            } else {
                $resetLanguageCode = $defaultLanguageCode;
            }
        } elseif ($this->userConfiguration->crowdinIdentifier) {
            $useT3 = str_contains($path, 'EXT:' . $this->userConfiguration->extensionKey);
            if (!$useT3 && $typo3Version >= 14) {
                // TYPO3 v14 is using a <domain>.<key> syntax as well (e.g., in Frontend)
                $useT3 = str_starts_with($path, $this->userConfiguration->extensionKey . '.');
            }
            if ($useT3) {
                $resetLanguageCode = 't3';
            } else {
                $resetLanguageCode = $defaultLanguageCode;
            }
        }

        // Actually reinitialize if needed
        if ($resetLanguageCode !== null) {
            $this->init($resetLanguageCode);
        }
    }

    protected function loadUserConfiguration(): void
    {
        if (!$this->userConfiguration) {
            $this->userConfiguration = GeneralUtility::makeInstance(UserConfiguration::class);
        }
    }
}
