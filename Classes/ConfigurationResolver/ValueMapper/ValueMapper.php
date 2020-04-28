<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Service\Registerable;

abstract class ValueMapper extends ConfigurationResolver implements ValueMapperInterface, Registerable
{
    protected function getResolverClass(): string
    {
        return ValueMapper::class;
    }

    protected function resolveValue($fieldValue, array $context)
    {
        return $fieldValue;
    }

    /**
     * @param array $context
     * @param string|FormFieldInterface|null $fieldValue
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context, $fieldValue = null)
    {
        if ($fieldValue === null) {
            $fieldValue = isset($context['data'][$context['key']])
                ? $context['data'][$context['key']]
                : null;
        }

        if ($fieldValue instanceof MultiValueFormField) {
            $result = [];
            foreach ($fieldValue as $key => $value) {
                $result[$key] = $this->resolve($context, $value);
            }
            $class = get_class($fieldValue);
            return new $class($result);
        } else {
            return $this->resolveValue($fieldValue, $context);
        }
    }
}
