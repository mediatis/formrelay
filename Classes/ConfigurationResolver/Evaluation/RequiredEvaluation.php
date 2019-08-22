<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class RequiredEvaluation extends Evaluation
{
    protected function convertScalarConfigToArray()
    {
        return true;
    }

    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        $fields = $this->config;
        foreach ($fields as $field) {
            if (!$context['data'][$field]) {
                return false;
            }
        }
        return true;
    }
}
