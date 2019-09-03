<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class SwitchValueMapper extends ValueMapper
{
    public function resolve(array $context): string
    {
        $valueMapper = null;
        foreach ($this->config as $case) {
            $caseValue = $case['case'] ?: $case['_typoScriptNodeValue'] ?: '';
            $caseResult = $case['value'] ?: '';
            if ($caseValue === $context['data'[$context['key']]]) {
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
