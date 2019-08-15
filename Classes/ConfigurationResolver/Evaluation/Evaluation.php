<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class Evaluation extends ConfigurationResolver implements EvaluationInterface, Registerable
{
    protected $then = null;
    protected $else = null;

    protected function getResolverClass()
    {
        return Evaluation::class;
    }

    /**
     * the method "resolve" is calling "eval" and depending on its result
     * it will try to return a "then" or "else" part of the config.
     * if the needed part is missing in the config, it will return null
     *
     * @param array $context
     * @param array $keysEvaluated
     * @return mixed|null
     */
    public function resolve(array $context = [], array $keysEvaluated = [])
    {
        if (is_array($this->config)) {
            if (isset($this->config['then'])) {
                $this->then = $this->config['then'];
                unset($this->config['then']);
            }
            if (isset($this->config['else'])) {
                $this->else = $this->config['else'];
                unset($this->config['else']);
            }
        }
        $result = $this->eval($context, $keysEvaluated);
        return $result ? $this->then : $this->else;
    }

    /**
     * the method "eval" is called to evaluate the expression defined in the config
     * it will always return a boolean value
     *
     * @param array $context
     * @param array $keysEvaluated
     * @return bool
     */
    abstract public function eval(array $context = [], array $keysEvaluated = []) : bool;
}
