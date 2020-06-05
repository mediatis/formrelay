<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class NegateValueMapper extends ValueMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function resolveValue($fieldValue, array $context): string
    {
        $config = $this->config;

        $true = '1';
        if (isset($config['true'])) {
            $true = $config['true'];
            unset($config['true']);
        }

        $false = '0';
        if (isset($config['false'])) {
            $false = $config['false'];
            unset($config['false']);
        }

        $valueMapper = $this->resolveKeyword('general', $this->config);
        $result = $valueMapper->resolve($context, $fieldValue);

        return !!$result ? $false : $true;
    }
}
