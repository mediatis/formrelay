<?php

namespace Mediatis\Formrelay\Service;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\GeneralFieldMapper;
use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\GeneralValueMapper;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class DataMapper implements SingletonInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ConfigurationManager */
    protected $configurationManager;

    protected $settings;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function process($data, $extKey, $index)
    {
        $this->settings = $this->configurationManager->getFormrelaySettings($extKey, $index);

        $result = $this->settings['defaults'] ?: [];

        $ignoreKeys = $this->settings['fields']['ignore'] ? explode(',', $this->settings['fields']['ignore']) : [];
        $ignoreEmptyValues = !!$this->settings['values']['ignoreIfEmpty'];

        foreach ($data as $key => $value) {
            // globally ignored keys are being ignored completely
            if (in_array($key, $ignoreKeys)) {
                continue;
            }

            // if you wish, we ignore empty values
            if ($ignoreEmptyValues && trim($value) === '') {
                continue;
            }

            // build context for mapper algorithms
            $context = ['key' => $key, 'data' => $data];

            // value mapping
            $valueMapper = $this->objectManager->get(
                GeneralValueMapper::class,
                $this->settings['values']['mapping'][$key] ?: []
            );
            $context['value'] = $valueMapper->process($context);

            // field mapping, which does the rest of the data mapping
            $fieldMapper = $this->objectManager->get(
                GeneralFieldMapper::class,
                $this->settings['fields']['mapping'][$key] ?: $this->settings['fields']['unmapped']
            );
            $fieldMapper->process($result, $context);
        }

        return $result;
    }
}
