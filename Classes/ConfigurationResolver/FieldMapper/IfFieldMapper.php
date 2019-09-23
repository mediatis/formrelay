<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class IfFieldMapper extends FieldMapper
{
    public function finish(&$context, &$result): bool
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        $evalResult = $evaluation->resolve($context);
        if ($evalResult !== null) {
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $evalResult);
            $result = $fieldMapper->resolve($context, $result);
            return true;
        }
        return false;
    }
}
