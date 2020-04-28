<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class RawValueMapper extends ValueMapper
{
    /**
     * @param array $context
     * @param string|FormFieldInterface|null $fieldValue
     * @return string|FormFieldInterface|null
     */
    public function resolveValue($fieldValue, array $context)
    {
        if (isset($this->config[$fieldValue])) {
            $valueMapper = $this->resolveKeyword('general', $this->config[$fieldValue]);
            $result = $valueMapper->resolve($context, $fieldValue);
            if ($result !== null) {
                return $result;
            }
        }
        return parent::resolveValue($fieldValue, $context);
    }
}
