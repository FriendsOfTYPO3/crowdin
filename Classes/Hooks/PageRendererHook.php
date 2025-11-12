<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Hooks;

use FriendsOfTYPO3\Crowdin\Traits\ConfigurationOptionsTrait;
use FriendsOfTYPO3\Crowdin\UserConfiguration;
use TYPO3\CMS\Core\Core\RequestId;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRendererHook
{
    use ConfigurationOptionsTrait;

    private const LANGUAGE_KEY = 't3';

    public function run(array &$params): void
    {
        if (static::getConfigurationOption('enable', '0') === '1') {
            $projectIdentifier = $this->getProjectIdentifier();
            if ($projectIdentifier) {
                $nonce = GeneralUtility::getContainer()->get(RequestId::class)->nonce->consume();
                $js = '
                        <script nonce="' . $nonce . '">
                            var _jipt = [];
                            _jipt.push(["project", ' . GeneralUtility::quoteJSvalue($projectIdentifier) . ']);
                        </script>
                        <script nonce="' . $nonce . '" src="https://cdn.crowdin.com/jipt/jipt.js"></script>';

                $params['jsLibs'] = $js . $params['jsLibs'];
            }
        }
    }

    protected function getProjectIdentifier(): string
    {
        $userConfiguration = GeneralUtility::makeInstance(UserConfiguration::class);
        if ($userConfiguration->usedForCore) {
            return 'typo3-cms';
        }

        return $userConfiguration->crowdinIdentifier;
    }
}
