<?php

namespace Mediatis\Formrelay;

use FormRelay\Core\CoreInitialization;
use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\DataProvider\AdwordsCampaignsDataProvider;
use Mediatis\Formrelay\DataProvider\AdwordsDataProvider;
use Mediatis\Formrelay\DataProvider\ContentElementDataProvider;
use Mediatis\Formrelay\DataProvider\LanguageCodeDataProvider;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class Initialization
{
    protected $objectManager;

    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function initialize(RegistryInterface $registry)
    {
        CoreInitialization::initialize($registry);
        $registry->registerDataProvider(AdwordsCampaignsDataProvider::class);
        $registry->registerDataProvider(AdwordsDataProvider::class);
        $registry->registerDataProvider(ContentElementDataProvider::class, [$this->objectManager]);
        $registry->registerDataProvider(LanguageCodeDataProvider::class);
    }
}
