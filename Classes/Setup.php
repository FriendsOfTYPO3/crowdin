<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class Setup
{
    private readonly string $overrideNamespace;

    public function __construct()
    {
        $typo3Version = (new Typo3Version())->getMajorVersion();
        if ($typo3Version >= 14) {
            $this->overrideNamespace = 'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override\\V14';
        } else {
            $this->overrideNamespace = 'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override\\V12';
        }
    }

    public function enable(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $localConfiguration = $configurationManager->getLocalConfiguration();

        $changesToBeWritten = false;
        if (!isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            $localConfiguration['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
            $changesToBeWritten = true;
        }

        $changesToBeWritten |= $this->disableLegacyOverrides($localConfiguration);
        if (!in_array($this->overrideNamespace, $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
            if (!in_array('TYPO3\\CMS\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3\\CMS\\Fluid\\ViewHelpers';
            }
            if (!in_array('TYPO3Fluid\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3Fluid\\Fluid\\ViewHelpers';
            }
            $localConfiguration['SYS']['fluid']['namespaces']['f'][] = $this->overrideNamespace;
            $changesToBeWritten = true;
        }

        if ($changesToBeWritten) {
            $configurationManager->writeLocalConfiguration($localConfiguration);
        }

    }

    public function disable(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $localConfiguration = $configurationManager->getLocalConfiguration();

        $changesToBeWritten = false;
        if (isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            unset($localConfiguration['SYS']['localization']['locales']['user']['t3']);
            $changesToBeWritten = true;
        }

        $changesToBeWritten |= $this->disableLegacyOverrides($localConfiguration);
        if (in_array($this->overrideNamespace, $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
            foreach ($localConfiguration['SYS']['fluid']['namespaces']['f'] as $k => $v) {
                if ($v === $this->overrideNamespace) {
                    unset($localConfiguration['SYS']['fluid']['namespaces']['f'][$k]);
                    $changesToBeWritten = true;
                }
            }
            if (count($localConfiguration['SYS']['fluid']['namespaces']['f'] ?? []) === 2) {
                unset($localConfiguration['SYS']['fluid']['namespaces']['f']);
            }
        }

        if ($changesToBeWritten) {
            $configurationManager->writeLocalConfiguration($localConfiguration);
        }
    }

    /**
     * Disables the legacy overrides from the configuration.
     *
     * @param array $localConfiguration
     * @return bool
     * @todo can be removed once v4.0 is "largely" in use
     */
    private function disableLegacyOverrides(array &$localConfiguration): bool
    {
        $legacyOverrideNamespaces = [
            // Version 3.x
            'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override',
            // Version 2.x or below
            'GeorgRinger\\Crowdin\\ViewHelpers\\Override',
        ];
        $changesToBeWritten = false;
        foreach ($legacyOverrideNamespaces as $legacyOverrideNamespace) {
            if (in_array($legacyOverrideNamespace, $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                foreach ($localConfiguration['SYS']['fluid']['namespaces']['f'] as $k => $v) {
                    if ($v === $legacyOverrideNamespace) {
                        unset($localConfiguration['SYS']['fluid']['namespaces']['f'][$k]);
                        $changesToBeWritten = true;
                    }
                }
            }
        }
        return $changesToBeWritten;
    }
}
