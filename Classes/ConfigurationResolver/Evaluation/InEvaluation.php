<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class InEvaluation extends Evaluation
{

    protected function convertScalarConfigToArray()
    {
        return true;
    }

    protected function evalValue($fieldValue, array $context = [], array $keysEvaluated = [])
    {
        return in_array($fieldValue, $this->config);
    }
}
