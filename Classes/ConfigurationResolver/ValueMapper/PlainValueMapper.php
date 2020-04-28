<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class PlainValueMapper extends ValueMapper
{
    public function resolveValue($fieldValue, array $context): string
    {
        return $this->config;
    }
}
