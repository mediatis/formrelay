<?php

namespace Mediatis\Formrelay\DataProvider;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Cookie implements DataProviderInterface
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

    /**
     * Adds field with value from a content element to the E-Mail dataArray
     *
     * @param array $dataArray
     * @return void
     */
    public function addData(array &$dataArray)
    {
        $settings = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
        $cookieFieldMap = $settings['settings']['dataProviders']['cookie']['cookieFieldMap'] ?? [];
        foreach ($cookieFieldMap as $cookie => $field) {
            if (isset($_COOKIE[$cookie])) {
                $dataArray[$field] = $_COOKIE[$cookie];
            }
        }
    }
}
