<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use DateTime;

class DateElementProcessor extends ElementProcessor
{
    const DATE_FORMAT = 'Y-m-d';

    protected function getElementType()
    {
        return 'Date';
    }

    protected function override()
    {
        return true;
    }

    protected function process($element, $elementValue)
    {
        $value = '';
        if ($elementValue instanceof DateTime) {
            $value = $elementValue->format(static::DATE_FORMAT);
        }
        return $value;
    }
}
