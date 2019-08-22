<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

interface EvaluationInterface
{
    /**
     * EvaluationInterface constructor.
     * @param array|string $config
     */
    public function __construct($config = []);

    /**
     * @param array $context
     * @param array $keysEvaluated
     * @return bool
     */
    public function eval(array $context = [], array $keysEvaluated = []): bool;
}
