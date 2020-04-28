<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class EmptyEvaluation extends Evaluation
{

    protected function evalValue($fieldValue, array $context = [], array $keysEvaluated = [])
    {
        return !!$fieldValue;
    }

    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        // not empty
        $result = parent::eval($context, $keysEvaluated);

        // empty
        if ($this->config) {
            $result = !$result;
        }

        return $result;
    }
}
