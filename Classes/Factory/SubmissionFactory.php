<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Model\Submission\Submission;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Route\RouteInterface;
use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class SubmissionFactory
{
    /** @var ConfigurationManagerInterface */
    protected $configurationManager;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    protected function getExtensionConfiguration(array $fullConfiguration, string $extensionKey)
    {
        $configuration = $fullConfiguration['plugin.'][$extensionKey . '.']['settings.'] ?? [];
        return ArrayUtility::convertTypoScriptArrayToPlainArray($configuration);
    }

    protected function buildGlobalConfiguration(RegistryInterface $registry): array
    {
        $fullConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $globalConfiguration = $this->getExtensionConfiguration($fullConfiguration, 'tx_formrelay');
        $routes = $registry->getRoutes();
        /** @var RouteInterface $route */
        foreach ($routes as $route) {
            $routeName = $route::getKeyword();
            $globalConfiguration['routes'][$routeName] = $this->getExtensionConfiguration(
                $fullConfiguration,
                'tx_formrelay' . $routeName
            );
        }
        return $globalConfiguration;
    }

    public function buildSubmission(RegistryInterface $registry, array $formData, array $formConfiguration): SubmissionInterface
    {
        $globalConfiguration = $this->buildGlobalConfiguration($registry);
        return new Submission(
            $formData,
            [
                ArrayUtility::convertConfigurationForRelayFormat($globalConfiguration),
                ArrayUtility::convertConfigurationForRelayFormat($formConfiguration),
            ]
        );
    }
}
