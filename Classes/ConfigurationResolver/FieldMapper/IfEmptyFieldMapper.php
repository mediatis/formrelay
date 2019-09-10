<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class IfEmptyFieldMapper extends FieldMapper
{

    public function finish(&$context, &$result): bool
    {
        if (isset($result[$context['mappedKey']])) {
            return true;
        }
        return false;
    }
}
