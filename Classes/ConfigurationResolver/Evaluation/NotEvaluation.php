<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class NotEvaluation extends Evaluation
{

    public function eval(array $context = [], array $keysEvaluated = [])
    {
        $evaluationClass = is_array($this->config) ? GeneralEvaluation::class : EqualsEvaluation::class;
        $evaluation = $this->objectManager->get($evaluationClass, $this->config);
        return !$evaluation->eval($context, $keysEvaluated);
    }
}
