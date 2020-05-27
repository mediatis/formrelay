<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

abstract class ElementProcessor implements ElementProcessorInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var array */
    protected $options;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function initializeObject()
    {
        $logManager = $this->objectManager->get(LogManager::class);
        $this->logger = $logManager->getLogger(static::class);
    }

    abstract protected function process($element, $elementValue);

    protected function getElementClass()
    {
        return '';
    }

    protected function getElementType()
    {
        return '';
    }

    protected function getValueClass()
    {
        return '';
    }

    protected function match($element, $elementValue)
    {
        $elementClass = $this->getElementClass();
        if ($elementClass && is_a($element, $elementClass)) {
            return true;
        }

        $elementType = $this->getElementType();
        if ($elementType && $element->getType() === $elementType) {
            return true;
        }

        $valueClass = $this->getValueClass();
        if ($valueClass && is_a($elementValue, $valueClass)) {
            return true;
        }

        return false;
    }

    protected function override()
    {
        return false;
    }

    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed)
    {
        $this->options = $options;
        if ((!$processed || $this->override()) && $this->match($element, $elementValue)) {
            $id = $element->getIdentifier();
            $name = $element->getProperties()['fluidAdditionalAttributes']['name'] ?: $id;
            $value = $this->process($element, $elementValue);
            $result[$name] = $value;
            $processed = true;
        }
    }
}
