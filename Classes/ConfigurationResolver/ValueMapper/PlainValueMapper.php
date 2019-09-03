<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class PlainValueMapper extends ValueMapper
{
    public function resolve(array $context): string
    {
        return $this->config;
    }
}
