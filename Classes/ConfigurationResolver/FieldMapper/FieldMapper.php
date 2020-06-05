<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Service\Registerable;

abstract class FieldMapper extends ConfigurationResolver implements FieldMapperInterface, Registerable
{
    protected function getResolverClass(): string
    {
        return FieldMapper::class;
    }

    protected function prepareValue($fieldValue, &$context, &$result)
    {
        return $fieldValue;
    }

    public function prepare(&$context, &$result)
    {
        $fieldValue = $context['value'];
        if ($fieldValue instanceof MultiValueFormField) {
            $multiValue = [];
            foreach ($fieldValue as $value) {
                $multiValue[] = $this->prepareValue($value, $context, $multiValue);
            }
            $class = get_class($fieldValue);
            $context['value'] = new $class($multiValue);
        } else {
            $context['value'] = $this->prepareValue($fieldValue, $context, $result);
        }
    }

    protected function finishValue($fieldValue, &$context, &$result)
    {
    }

    public function finish(&$context, &$result): bool
    {
        $fieldValue = $context['value'];
        if ($fieldValue instanceof MultiValueFormField) {
            foreach ($fieldValue as $value) {
                $this->finishValue($value, $context, $result);
            }
        } else {
            $this->finishValue($fieldValue, $context, $result);
        }
        return false;
    }
}
