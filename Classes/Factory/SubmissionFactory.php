<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Model\Submission\Submission;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Route\RouteInterface;
use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\Configuration\BaseConfigurationUpdaterInterface;
use Mediatis\Formrelay\Configuration\RouteConfigurationUpdaterInterface;
use Mediatis\Formrelay\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class SubmissionFactory
{
    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var ConfigurationManagerInterface */
    protected $configurationManager;

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    protected function getExtensionConfiguration(array $fullConfiguration, string $extensionKey)
    {
        $configuration = $fullConfiguration[$extensionKey . '.']['settings.'] ?? [];
        return ArrayUtility::convertTypoScriptArrayToPlainArray($configuration);
    }

    protected function buildFormRelayConfiguration(RegistryInterface $registry, array $typoScriptConfiguration): array
    {
        $configuration = $this->getExtensionConfiguration($typoScriptConfiguration, 'tx_formrelay');
        $this->signalSlotDispatcher->dispatch(BaseConfigurationUpdaterInterface::class, BaseConfigurationUpdaterInterface::SIGNAL_UPDATE_BASE_CONFIGURATION, [&$configuration]);
        $routes = $registry->getRoutes();
        /** @var RouteInterface $route */
        foreach ($routes as $route) {
            $routeName = $route::getKeyword();
            $routeConfiguration = $this->getExtensionConfiguration($typoScriptConfiguration, 'tx_formrelay_' . $routeName);
            $this->signalSlotDispatcher->dispatch(RouteConfigurationUpdaterInterface::class, RouteConfigurationUpdaterInterface::SIGNAL_UPDATE_ROUTE_CONFIGURATION, [$routeName, &$routeConfiguration]);
            if (array_key_exists('passes', $routeConfiguration)) {
                foreach (array_keys($routeConfiguration['passes']) as $pass) {
                    $this->signalSlotDispatcher->dispatch(RouteConfigurationUpdaterInterface::class, RouteConfigurationUpdaterInterface::SIGNAL_UPDATE_ROUTE_CONFIGURATION, [$routeName, &$routeConfiguration['passes'][$pass]]);
                }
            }
            $configuration['routes'][$routeName] = $routeConfiguration;
        }
        return $configuration;
    }

    public function buildSubmission(RegistryInterface $registry, array $formData, array $formConfiguration): SubmissionInterface
    {
        $fullConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)['plugin.'] ?? [];
        $globalConfiguration = $this->buildFormRelayConfiguration($registry, $fullConfiguration);
        $formConfiguration = $this->buildFormRelayConfiguration($registry, $formConfiguration);
        return new Submission(
            $formData,
            [
                ArrayUtility::convertConfigurationForRelayFormat($globalConfiguration),
                ArrayUtility::convertConfigurationForRelayFormat($formConfiguration),
            ]
        );
    }
}
