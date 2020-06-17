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
            $name = $this->getElementName($element);
            if (array_key_exists($name, $result)) {
                unset($result[$name]);
            }
            $processed = true;
        }
    }
}
