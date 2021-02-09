<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use FormRelay\Core\Model\Form\MultiValueField;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class GenericElementProcessor extends ElementProcessor
{
    protected function getElementClass()
    {
        return GenericFormElement::class;
    }

    protected function process($element, $elementValue)
    {
        if ($element->getType() === 'Checkbox' && !$elementValue) {
            $elementValue = 0;
        }
        return is_array($elementValue) ? new MultiValueField($elementValue) : $elementValue;
    }
}
