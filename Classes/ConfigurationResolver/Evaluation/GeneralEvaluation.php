<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\GeneralConfigurationResolverInterface;

class GeneralEvaluation extends Evaluation implements GeneralConfigurationResolverInterface
{
    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        $evaluation = $this->objectManager->get(AndEvaluation::class, $this->config);
        return $evaluation->eval($context, $keysEvaluated);
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
    public function resolve(array $context, array $keysEvaluated = [])
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
}
