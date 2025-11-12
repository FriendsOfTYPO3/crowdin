<?php

declare(strict_types=1);

use FriendsOfTYPO3\Crowdin\Backend\ToolbarItems\CrowdinToolbarItem;
use FriendsOfTYPO3\Crowdin\Command\DisableCommand;
use FriendsOfTYPO3\Crowdin\Command\EnableCommand;
use FriendsOfTYPO3\Crowdin\EventListener\AfterPackageActivation;
use FriendsOfTYPO3\Crowdin\EventListener\AfterPackageDeactivation;
use FriendsOfTYPO3\Crowdin\Xclass\V12;
use FriendsOfTYPO3\Crowdin\Xclass\V14;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->load('FriendsOfTYPO3\Crowdin\\', __DIR__ . '/../Classes/*')
        ->exclude([
            __DIR__ . '/../Classes/Xclass/**/*.php',
        ]);

    $services->set(CrowdinToolbarItem::class)
        ->public();

    $services->set(AfterPackageActivation::class)
        ->tag('event.listener', [
            'identifier' => 'core-afterpackageactivation-crowdin',
            'event' => AfterPackageActivationEvent::class,
        ]);

    $services->set(AfterPackageDeactivation::class)
        ->tag('event.listener', [
            'identifier' => 'core-afterpackagedeactivation-crowdin',
            'event' => AfterPackageDeactivationEvent::class,
        ]);

    $services->set(EnableCommand::class)
        ->tag('console.command', [
            'command' => 'crowdin:enable',
            'schedulable' => false,
            'description' => 'Enable Crowdin',
        ]);

    $services->set(DisableCommand::class)
        ->tag('console.command', [
            'command' => 'crowdin:disable',
            'schedulable' => false,
            'description' => 'Disable Crowdin',
        ]);

    if ((new Typo3Version())->getMajorVersion() >= 14) {
        $services->set(V14\LanguageServiceFactoryXclassed::class)
            ->public()
            ->arg('$runtimeCache', service('cache.runtime'));
    } else {
        $services->set(V12\LanguageServiceFactoryXclassed::class)
            ->public()
            ->arg('$runtimeCache', service('cache.runtime'));
    }

    $services->set('cache.runtime', FrontendInterface::class)
        ->factory([
            service(CacheManager::class),
            'getCache',
        ])
        ->args([
            'runtime',
        ]);
};
