<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class PlainFieldMapper extends FieldMapper
{
    public function prepare(&$context, &$result)
    {
        $context['mappedKey'] = $this->config;
    }

    public function finish(&$context, &$result): bool
    {
        $result[$context['mappedKey']] = $context['value'];
        return true;
    }
}
