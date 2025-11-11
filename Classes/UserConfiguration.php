<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserConfiguration
{
    use Traits\ConfigurationOptionsTrait;

    public readonly bool $usedForCore;
    public readonly ?string $crowdinIdentifier;
    public readonly ?string $extensionKey;

    public function __construct()
    {
        $config = GeneralUtility::makeInstance(ConfigurationLoader::class);
        $crowdinConfiguration = $config->get();

        if (static::getConfigurationOption('enable', '0') === '0') {
            $this->usedForCore = false;
            $this->crowdinIdentifier = null;
            $this->extensionKey = null;
            return;
        }

        $extensionKey = static::getConfigurationOption('extension', 'typo3');
        if ($extensionKey === 'typo3') {
            $this->usedForCore = true;
            $this->crowdinIdentifier = null;
            $this->extensionKey = null;
        } else {
            $crowdinConfiguration = GeneralUtility::makeInstance(ConfigurationLoader::class)->get();

            if (isset($crowdinConfiguration[$extensionKey])) {
                $this->usedForCore = false;
                $this->crowdinIdentifier = $crowdinConfiguration[$extensionKey];
                $this->extensionKey = $extensionKey;
            } else {
                // @todo log error?
                $this->usedForCore = false;
                $this->crowdinIdentifier = null;
                $this->extensionKey = null;
            }
        }
    }
}
