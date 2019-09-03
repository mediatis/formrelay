<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class PassthroughFieldMapper extends FieldMapper
{
    public function finish(&$context, &$result)
    {
        $result[$context['key']] = $context['value'];
        return true;
    }
}
