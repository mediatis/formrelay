<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class NegateFieldMapper extends FieldMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function prepare(&$result, &$context)
    {
        $context['value'] = !!$context['value']
            ? ($this->config['false'] ?: '0')
            : ($this->config['true'] ?: '1');
    }
}
