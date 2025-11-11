<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class ConfigurationLoader
{
    /**
     * @return string[]
     */
    public function get(): array
    {
        $identifier = 'crowdin-cache-list';
        /** @var FrontendInterface $cache */
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('assets');
        $data = $cache->get($identifier);
        if (!$data) {
            $data = [];
            $list = json_decode((string)GeneralUtility::getUrl('https://localize.typo3.org/xliff/status.json'), true);
            foreach ($list['projects'] ?? [] as $project) {
                if ($project['extensionKey'] === 'typo3-cms' || !($project['usable'] ?? false)) {
                    continue;
                }
                $data[$project['extensionKey']] = $project['crowdinKey'];
            }
            $cache->set($identifier, json_encode($data));
        } else {
            $data = json_decode($data, true);
        }

        return $data;
    }
}
