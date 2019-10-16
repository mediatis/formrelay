<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class IfValueMapper extends ValueMapper
{

    /**
     * @param array $context
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context)
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        if ($evaluation) {
            $result = $evaluation->resolve($context);
            if ($result !== null) {
                $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $result);
                return $valueMapper->resolve($context);
            }
        }
        return null;
    }
}
