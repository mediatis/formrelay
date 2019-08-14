<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class DistributeFieldMapper extends FieldMapper
{
    public function finish(&$result, &$context)
    {
        if (!is_array($this->config)) {
            $this->config = ['fields' => explode(',', $this->config)];
        } elseif (!is_array($this->config['fields'])) {
            $this->config['fields'] = explode(',', $this->config['fields']);
        }
        foreach ($this->config['fields'] as $field) {
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $field);
            $fieldMapper->process($result, $context);
        }
        return true;
    }
}
