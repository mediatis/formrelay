<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use Mediatis\Formrelay\Configuration\ConfigurationManager;

abstract class ElementProcessor implements ElementProcessorInterface
{
    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var array */
    protected $options;

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    abstract protected function getElementClass();

    abstract protected function process($element, $elementValue);

    protected function match($element, $elementValue)
    {
        return is_a($element, $this->getElementClass());
    }

    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed)
    {
        $this->options = $options;
        if (!$processed && $this->match($element, $elementValue)) {
            $id = $element->getIdentifier();
            $name = $element->getProperties()['fluidAdditionalAttributes']['name'] ?: $id;
            $value = $this->process($element, $elementValue);
            if ($value !== null) {
                $result[$name] = $value;
            }
            $processed = true;
        }
    }
}
