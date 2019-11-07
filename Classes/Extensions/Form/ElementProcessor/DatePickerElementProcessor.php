<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use DateTime;
use TYPO3\CMS\Form\Domain\Model\FormElements\DatePicker;

class DatePickerElementProcessor extends ElementProcessor
{
    protected function getElementClass()
    {
        return DatePicker::class;
    }

    protected function override()
    {
        return true;
    }

    protected function process($element, $elementValue)
    {
        $value = '';
        $properties = $element->getProperties();
        if ($elementValue instanceof DateTime) {
            if (isset($properties['dateFormat'])) {
                $dateFormat = $properties['dateFormat'];
                if (isset($properties['displayTimeSelector']) && $properties['displayTimeSelector'] === true) {
                    $dateFormat .= ' H:i';
                }
            } else {
                $dateFormat = DateTime::W3C;
            }
            $value = $elementValue->format($dateFormat);
        }
        return $value;
    }
}
