<?php

namespace Mediatis\Formrelay\Domain\Model;

class FormFieldMultiValue extends \ArrayObject
{
    public function __toString()
    {
        return implode(',', iterator_to_array($this));
    }
}
