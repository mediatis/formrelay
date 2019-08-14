<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class PassthroughFieldMapper extends FieldMapper
{
    public function finish(&$result, &$context)
    {
        $result[$context['key']] = $context['value'];
        return true;
    }
}
