<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class IfValueMapper extends ValueMapper
{

    /**
     * @param array $context
     * @param string|FormFieldInterface|null $fieldValue
     * @return string|FormFieldInterface|null
     */
    public function resolveValue($fieldValue, array $context)
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        if ($evaluation) {
            $result = $evaluation->resolve($context);
            if ($result !== null) {
                $valueMapper = $this->resolveKeyword('general', $result);
                return $valueMapper->resolve($context, $fieldValue);
            }
        }
        return null;
    }
}
