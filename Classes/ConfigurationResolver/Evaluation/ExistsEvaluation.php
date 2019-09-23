<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class ExistsEvaluation extends Evaluation
{

    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        // does not exist
        $result = !isset($context['data'][$context['key']]);

        // exists
        if ($this->config) {
            $result = !$result;
        }

        return $result;
    }
}
