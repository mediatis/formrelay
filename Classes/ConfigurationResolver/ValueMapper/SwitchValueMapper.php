<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class SwitchValueMapper extends ValueMapper
{
    /**
     * @param array $context
     * @param string|FormFieldInterface|null $fieldValue
     * @return string|FormFieldInterface|null
     */
    public function resolveValue($fieldValue, array $context)
    {
        $valueMapper = null;
        foreach ($this->config as $case) {
            $caseValue = $case['case'] ?: $case['_typoScriptNodeValue'] ?: '';
            $caseResult = $case['value'] ?: '';
            if ($caseValue === $fieldValue) {
                $valueMapper = $this->resolveKeyword('general', $caseResult);
                break;
            }
        }
        if ($valueMapper) {
            return $valueMapper->resolve($context, $fieldValue);
        }
        return parent::resolveValue($fieldValue, $context);
    }
}
