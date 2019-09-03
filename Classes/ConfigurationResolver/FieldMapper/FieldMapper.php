<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class FieldMapper extends ConfigurationResolver implements FieldMapperInterface, Registerable
{
    protected function getResolverClass(): string
    {
        return FieldMapper::class;
    }

    public function prepare(&$context, &$result)
    {
    }

    public function finish(&$context, &$result)
    {
        return false;
    }

    public function resolve(array $context, array $result = []): array
    {
        $this->prepare($context, $result);
        $this->finish($context, $result);
        return $result;
    }
}
