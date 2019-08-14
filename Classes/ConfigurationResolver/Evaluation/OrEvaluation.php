<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class OrEvaluation extends AndEvaluation
{

    protected function initialValue()
    {
        return false;
    }

    protected function calculate($result, $evaluation, $context, $keysEvaluated)
    {
        return $result || $evaluation->eval($context, $keysEvaluated);
    }
}
