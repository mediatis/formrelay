<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class EqualsEvaluation extends Evaluation
{
    protected function evalValue($fieldValue, array $context = [], array $keysEvaluated = [])
    {
        return $fieldValue === $this->config;
    }
}
