<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class NegateValueMapper extends ValueMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function resolve(array $context): string
    {
        $true = '1';
        if (isset($this->config['true'])) {
            $true = $this->config['true'];
            unset($this->config['true']);
        }

        $false = '0';
        if (isset($this->config['false'])) {
            $false = $this->config['false'];
            unset($this->config['false']);
        }

        $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $this->config);
        $result = $valueMapper->process($context);

        return !!$result ? $false : $true;
    }
}
