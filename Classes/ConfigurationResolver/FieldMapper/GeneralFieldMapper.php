<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\GeneralConfigurationResolverInterface;

class GeneralFieldMapper extends FieldMapper implements GeneralConfigurationResolverInterface
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
        $this->prepare($context, $result);
        $this->finish($context, $result);
        return $result;
    }

    public function prepare(&$context, &$result)
    {
        foreach ($this->fieldMappers as $fieldMapper) {
            $fieldMapper->prepare($context, $result);
        }
    }

    public function finish(&$context, &$result): bool
    {
        foreach ($this->fieldMappers as $fieldMapper) {
            if ($fieldMapper->finish($context, $result)) {
                return true;
            }
        }
        return false;
    }
}
