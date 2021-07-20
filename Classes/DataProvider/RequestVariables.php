<?php

namespace Mediatis\Formrelay\DataProvider;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class RequestVariables implements DataProviderInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ConfigurationManager */
    protected $configurationManager;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function addData(array &$data)
    {
        $settings = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
        $variableFieldMap = $settings['settings']['dataProviders']['requestVariables']['variableFieldMap'] ?? [];

        foreach ($variableFieldMap as $variable => $field) {
            $value = GeneralUtility::getIndpEnv($variable);
            if (strlen((string) $value) > 0) {
                $data[$field] = $value;
            }
        }
    }
}
