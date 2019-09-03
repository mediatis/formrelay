<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class IfValueMapper extends ValueMapper
{

    public function resolve(array $context): string
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        if ($evaluation) {
            $result = $evaluation->resolve($context);
            if ($result !== null) {
                $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $result);
                return $valueMapper->process($context);
            }
        }
        return null;
    }
}
