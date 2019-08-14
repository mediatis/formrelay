<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class EqualsEvaluation extends Evaluation
{

    public function eval(array $context = [], array $keysEvaluated = [])
    {
        return $context['data'][$context['key']] === $this->config;
    }
}
