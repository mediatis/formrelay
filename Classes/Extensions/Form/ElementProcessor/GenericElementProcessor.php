<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
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
        return is_array($elementValue) ? new MultiValueFormField($elementValue) : $elementValue;
    }
}
