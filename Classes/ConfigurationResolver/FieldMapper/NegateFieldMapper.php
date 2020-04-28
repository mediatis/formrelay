<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class NegateFieldMapper extends FieldMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    protected function prepareValue($fieldValue, &$context, &$result)
    {
        return !!$fieldValue
            ? ($this->config['false'] ?: '0')
            : ($this->config['true'] ?: '1');
    }
}
