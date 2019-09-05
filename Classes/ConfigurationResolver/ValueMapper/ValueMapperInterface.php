<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolverInterface;

interface ValueMapperInterface extends ConfigurationResolverInterface
{
    /**
     * @param array $context
     * @return string|null
     */
    public function resolve(array $context);
}
