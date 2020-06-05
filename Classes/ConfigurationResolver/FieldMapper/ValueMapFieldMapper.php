<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\GeneralValueMapper;

class ValueMapFieldMapper extends FieldMapper
{
    public function prepare(&$context, &$result)
    {
        $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $this->config);
        $context['value'] = $valueMapper->resolve($context);
    }
}
