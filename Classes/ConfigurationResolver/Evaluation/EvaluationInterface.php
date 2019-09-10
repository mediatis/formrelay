<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolverInterface;

interface EvaluationInterface extends ConfigurationResolverInterface
{
    /**
     * @param array $context
     * @param array $keysEvaluated
     * @return bool
     */
    public function eval(array $context = [], array $keysEvaluated = []): bool;
}
