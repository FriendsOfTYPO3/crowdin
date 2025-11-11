<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class Setup
{
    public function enable(): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $localConfiguration = $configurationManager->getLocalConfiguration();

        $changesToBeWritten = false;
        if (!isset($localConfiguration['SYS']['localization']['locales']['user']['t3'])) {
            $localConfiguration['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
            $changesToBeWritten = true;
        }

        if (!in_array('FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
            if (!in_array('TYPO3\\CMS\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3\\CMS\\Fluid\\ViewHelpers';
            }
            if (!in_array('TYPO3Fluid\\Fluid\\ViewHelpers', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
                $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'TYPO3Fluid\\Fluid\\ViewHelpers';
            }
            $localConfiguration['SYS']['fluid']['namespaces']['f'][] = 'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override';
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
        if (in_array('FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override', $localConfiguration['SYS']['fluid']['namespaces']['f'] ?? [], true)) {
            foreach ($localConfiguration['SYS']['fluid']['namespaces']['f'] as $k => $v) {
                if ($v === 'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override') {
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

}
