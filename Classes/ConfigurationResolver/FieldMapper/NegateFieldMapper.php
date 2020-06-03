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
        $true = $this->config['true'] ?: '1';
        $false = $this->config['false'] ?: '0';
        return !!$fieldValue ? $false : $true;
    }
}
