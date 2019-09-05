<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolverInterface;

interface FieldMapperInterface extends ConfigurationResolverInterface
{
    public function prepare(&$context, &$result);
    public function finish(&$context, &$result): bool;
}
