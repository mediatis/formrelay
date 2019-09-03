<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class RawValueMapper extends ValueMapper
{
    public function resolve(array $context): string
    {
        foreach ($this->config as $key => $value) {
            $valueMapper = null;

            if ($key === $context['data'[$context['key']]]) {
                $valueMapper = $this->resolveKeyword('general', $value);
            }

            if ($valueMapper) {
                $result = $valueMapper->resolve($context);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return parent::resolve($context);
    }
}
