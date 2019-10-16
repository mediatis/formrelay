<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class SwitchValueMapper extends ValueMapper
{
    /**
     * @param array $context
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context)
    {
        $valueMapper = null;
        foreach ($this->config as $case) {
            $caseValue = $case['case'] ?: $case['_typoScriptNodeValue'] ?: '';
            $caseResult = $case['value'] ?: '';
            if ($caseValue === $context['data'][$context['key']]) {
                $valueMapper = $this->resolveKeyword('general', $caseResult);
                break;
            }
        }
        if ($valueMapper) {
            return $valueMapper->resolve($context);
        }
        return parent::resolve($context);
    }
}
