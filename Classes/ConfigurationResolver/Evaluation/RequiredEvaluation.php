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
        $result = true;
        foreach ($this->config as $requiredField) {
            if (!isset($context['data'][$requiredField])) {
                $result = false;
                break;
            }
            if (!$context['data'][$requiredField]) {
                $result = false;
                break;
            }
            if (
                $context['data'][$requiredField] instanceof MultiValueFormField
                && count($context['data'][$requiredField]) === 0
            ) {
                $result = false;
                break;
            }
        }
        return $result;
    }
}
