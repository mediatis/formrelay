<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

interface ElementProcessorInterface
{
    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed);
}
