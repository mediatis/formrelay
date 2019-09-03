<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class GeneralFieldMapper extends FieldMapper
{
    protected $fieldMappers = [];

    public function resolve(array $context, array $result = []): array
    {
        $this->fieldMappers = [];
        $config = $this->preprocessConfigurationArray(['if'], ['plain']);
        foreach ($config as $key => $value) {
            $fieldMapper = $this->resolveKeyword($key, $value);
            if (!$fieldMapper && is_numeric($key)) {
                $fieldMapper = $this->resolveKeyword('general', $value);
            }
            if ($fieldMapper) {
                $this->fieldMappers[] = $fieldMapper;
            }
        }
        return parent::resolve($context, $result);
    }

    public function prepare(&$context, &$result) {
        foreach ($this->fieldMappers as $fieldMapper) {
            $fieldMapper->prepare($context, $result);
        }
    }

    public function finish(&$context, &$result)
    {
        foreach ($this->fieldMappers as $fieldMapper) {
            if ($fieldMapper->finish($context, $result)) {
                break;
            }
        }
        return true;
    }
}
