<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

interface ValueMapperInterface
{
    public function resolve(array $context);
}
