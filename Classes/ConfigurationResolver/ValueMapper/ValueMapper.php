<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class ValueMapper extends ConfigurationResolver implements ValueMapperInterface, Registerable
{
    abstract public function process($context);

    protected function getResolverClass()
    {
        return ValueMapper::class;
    }
}
