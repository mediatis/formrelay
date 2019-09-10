<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class DistributeFieldMapper extends FieldMapper
{
    public function convertScalarConfigToArray()
    {
        return true;
    }

    public function finish(&$context, &$result): bool
    {
         foreach ($this->config as $field) {
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $field);
            $fieldMapper->resolve($context, $result);
        }
        return true;
    }
}
