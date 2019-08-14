<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class EmptyEvaluation extends Evaluation
{

    public function eval(array $context = [], array $keysEvaluated = [])
    {
        // not empty
        $result = !!$context['data'][$context['key']];

        // empty
        if ($this->config) {
            $result = !$result;
        }

        return $result;
    }
}
