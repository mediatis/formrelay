<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class FieldMapper extends ConfigurationResolver implements FieldMapperInterface, Registerable
{
    protected function getResolverClass()
    {
        return FieldMapper::class;
    }

    public function prepare(&$result, &$context)
    {
    }

    public function finish(&$result, &$context)
    {
        return false;
    }
}
