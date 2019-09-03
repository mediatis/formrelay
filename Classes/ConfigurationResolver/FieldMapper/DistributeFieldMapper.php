<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class DistributeFieldMapper extends FieldMapper
{
    public function finish(&$context, &$result)
    {
        if (!is_array($this->config)) {
            $this->config = ['fields' => explode(',', $this->config)];
        } elseif (!is_array($this->config['fields'])) {
            $this->config['fields'] = explode(',', $this->config['fields']);
        }
        foreach ($this->config['fields'] as $field) {
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $field);
            $fieldMapper->resolve($context, $result);
        }
        return true;
    }
}
