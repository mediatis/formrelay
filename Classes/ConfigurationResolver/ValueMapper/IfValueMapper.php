<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class IfValueMapper extends ValueMapper
{

    public function process($context)
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        if ($evaluation) {
            $result = $evaluation->resolve($context);
            if (gettype($result !== 'boolean')) {
                $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $result);
                return $valueMapper->process($context);
            }
        }
        return false;
    }
}
