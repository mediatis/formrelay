<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class PlainFieldMapper extends FieldMapper
{
    protected function getKeyword()
    {
        return '';
    }

    public function prepare(&$result, &$context)
    {
        $context['mappedKey'] = $this->config;
    }

    public function finish(&$result, &$context)
    {
        $result[$context['mappedKey']] = $context['value'];
        return true;
    }
}
