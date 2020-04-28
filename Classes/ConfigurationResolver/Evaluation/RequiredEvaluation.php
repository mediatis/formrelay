<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class RequiredEvaluation extends Evaluation
{
    protected function convertScalarConfigToArray()
    {
        return true;
    }

    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        foreach ($this->config as $requiredField) {
            if (!isset($context['data'][$requiredField])) {
                return false;
            }
            if (!$context['data'][$requiredField]) {
                return false;
            }
            if (
                $context['data'][$requiredField] instanceof MultiValueFormField
                && count($context['data'][$requiredField]) === 0
            ) {
                return false;
            }
        }
        return true;
    }
}
