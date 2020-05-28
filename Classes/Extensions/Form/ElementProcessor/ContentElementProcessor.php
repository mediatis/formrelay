<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

class ContentElementProcessor extends IgnoredElementProcessor
{
    protected function getElementType()
    {
        return 'ContentElement';
    }
}
