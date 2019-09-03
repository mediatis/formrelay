<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class GeneralEvaluation extends Evaluation
{
    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        $evaluation = $this->objectManager->get(AndEvaluation::class, $this->config);
        return $evaluation->eval($context, $keysEvaluated);
    }
}
