<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

abstract class IgnoredElementProcessor extends ElementProcessor
{
    protected function process($element, $elementValue)
    {
        return null;
    }

    protected function override()
    {
        return true;
    }

    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed)
    {
        $this->options = $options;
        if ((!$processed || $this->override()) && $this->match($element, $elementValue)) {
            $id = $element->getIdentifier();
            $name = $id;
            if (method_exists($element, 'getProperties')) {
                $name = $element->getProperties()['fluidAdditionalAttributes']['name'] ?: $id;
            }
            if (isset($result[$name])) {
                unset($result[$name]);
            }
            $processed = true;
        }
    }
}
