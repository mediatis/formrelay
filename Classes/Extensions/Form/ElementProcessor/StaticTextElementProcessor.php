<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

class StaticTextElementProcessor extends IgnoredElementProcessor
{
    protected function getElementType()
    {
        return 'StaticText';
    }
}
