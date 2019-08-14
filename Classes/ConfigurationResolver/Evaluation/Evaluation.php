<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class Evaluation extends ConfigurationResolver implements EvaluationInterface, Registerable
{
    protected $then = true;
    protected $else = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (is_array($this->config)) {
            $this->then = true;
            if (isset($this->config['then'])) {
                $this->then = $this->config['then'];
                unset($this->config['then']);
            }
            $this->else = false;
            if (isset($this->config['else'])) {
                $this->else = $this->config['else'];
                unset($this->config['else']);
            }
        }
    }

    protected function getResolverClass()
    {
        return Evaluation::class;
    }

    public function resolve(array $context = [], array $keysEvaluated = [])
    {
        $result = $this->eval($context, $keysEvaluated);
        return $result ? $this->then : $this->else;
    }

    abstract public function eval(array $context = [], array $keysEvaluated = []);
}
