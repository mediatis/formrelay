<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class InEvaluation extends Evaluation
{

    protected function convertScalarConfigToArray()
    {
        return true;
    }

    public function eval(array $context = [], array $keysEvaluated = []) : bool
    {
        return in_array(
            $context['data'][$context['key']],
            $this->config
        );
    }
}
