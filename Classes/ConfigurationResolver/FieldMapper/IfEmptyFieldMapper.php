<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class IfEmptyFieldMapper extends FieldMapper
{

    public function finish(&$result, &$context)
    {
        if (isset($result[$context['mappedKey']])) {
            return true;
        }
        return false;
    }
}
