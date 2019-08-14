<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class IfFieldMapper extends FieldMapper
{

    /** @var GeneralFieldMapper */
    protected $fieldMapper;

    public function finish(&$result, &$context)
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        $evalResult = $evaluation->resolve($context);
        if (gettype($evalResult) !== 'boolean') {
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $evalResult);
            $fieldMapper->process($result, $context);
            return true;
        }
        return false;
    }
}
