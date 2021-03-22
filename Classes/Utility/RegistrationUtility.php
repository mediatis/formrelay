<?php

namespace Mediatis\Formrelay\Utility;

use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\Configuration\BaseConfigurationUpdaterInterface;
use Mediatis\Formrelay\Configuration\RouteConfigurationUpdaterInterface;
use Mediatis\Formrelay\Extensions\Form\FormDataProcessor;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class RegistrationUtility
{
    public static function registerInitialization(string $class, $method = 'initialize')
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $dispatcher->connect(
            RegistryInterface::class,
            RegistryFactory::SIGNAL_UPDATE_REGISTRY,
            $class,
            $method
        );
    }

    public static function registerBaseConfigurationUpdater(string $class, $method = 'updateBaseConfiguration')
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        // configuration updater
        $dispatcher->connect(
            BaseConfigurationUpdaterInterface::class,
            BaseConfigurationUpdaterInterface::SIGNAL_UPDATE_BASE_CONFIGURATION,
            $class,
            $method
        );
    }

    public static function registerRouteConfigurationUpdater(string $class, $method = 'updateRouteConfiguration')
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        // configuration updater
        $dispatcher->connect(
            RouteConfigurationUpdaterInterface::class,
            RouteConfigurationUpdaterInterface::SIGNAL_UPDATE_ROUTE_CONFIGURATION,
            $class,
            $method
        );
    }

    public static function registerFormElementProcessor($class, $method = 'processFormElement')
    {
        if (ExtensionManagementUtility::isLoaded('form')) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $dispatcher->connect(
                FormDataProcessor::class,
                FormDataProcessor::SIGNAL_PROCESS_FORM_ELEMENT,
                $class,
                $method
            );
        }
    }
}
