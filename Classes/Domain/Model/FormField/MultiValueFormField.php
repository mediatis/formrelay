<?php

namespace Mediatis\Formrelay\Domain\Model\FormField;

use ArrayObject;

class MultiValueFormField extends ArrayObject implements FormFieldInterface
{
    public function __toString() : string
    {
        return implode(',', iterator_to_array($this));
    }
}
