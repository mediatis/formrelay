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
        $elementType = $this->getElementType();
        $valueClass = $this->getValueClass();

        $result = false;
        if ($elementClass && is_a($element, $elementClass)) {
            $result = true;
        } elseif ($elementType && $element->getType() === $elementType) {
            $result = true;
        } elseif ($valueClass && is_a($elementValue, $valueClass)) {
            $result = true;
        }
        return $result;
    }

    protected function override()
    {
        return false;
    }

    protected function getElementName($element)
    {
        $name = $element->getIdentifier();
        if (method_exists($element, 'getProperties')) {
            $properties = $element->getProperties();
            if (isset($properties['fluidAdditionalAttributes']['name'])) {
                $name = $properties['fluidAdditionalAttributes']['name'];
            }
        }
        return $name;
    }

    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed)
    {
        $this->options = $options;
        if ((!$processed || $this->override()) && $this->match($element, $elementValue)) {
            $name = $this->getElementName($element);
            $value = $this->process($element, $elementValue);
            $result[$name] = $value;
            $processed = true;
        }
    }
}
