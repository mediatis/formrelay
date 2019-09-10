<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class Evaluation extends ConfigurationResolver implements EvaluationInterface, Registerable
{
    protected $then = null;
    protected $else = null;

    protected function getResolverClass(): string
    {
        return Evaluation::class;
    }

    /**
     * the method "eval" is called to evaluate the expression defined in the config
     * it will always return a boolean value
     *
     * @param array $context
     * @param array $keysEvaluated
     * @return bool
     */
    abstract public function eval(array $context = [], array $keysEvaluated = []): bool;
}
