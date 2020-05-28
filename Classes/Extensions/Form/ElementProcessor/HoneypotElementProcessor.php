<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

class HoneypotElementProcessor extends IgnoredElementProcessor
{
    protected function getElementType()
    {
        return 'Honeypot';
    }
}
