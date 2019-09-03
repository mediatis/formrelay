<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class ValueMapper extends ConfigurationResolver implements ValueMapperInterface, Registerable
{
    protected function getResolverClass(): string
    {
        return ValueMapper::class;
    }

    public function resolve(array $context): string
    {
        return $context['data'][$context['key']];
    }
}
