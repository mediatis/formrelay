<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Service\Registerable;

abstract class Evaluation extends ConfigurationResolver implements EvaluationInterface, Registerable
{
    protected $then = null;
    protected $else = null;

    protected function getResolverClass(): string
    {
        return Evaluation::class;
    }

    protected function evalValue($fieldValue, array $context = [], array $keysEvaluated = [])
    {
        return true;
    }

    /**
     * if a multi-value field is evaluated, a disjunction means that
     * the whole evaluation is true if at least one evaluation
     * for one of the values of that field is true (or-condition)
     *
     * @return bool
     */
    protected function multiValueIsDisjunctive()
    {
        return true;
    }

    /**
     * the method "eval" is called to evaluate the expression defined in the config
     * it will always return a boolean value
     *
     * @param array $context
     * @param array $keysEvaluated
     * @return bool
     */
    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        $fieldValue = $context['data'][$context['key']];

        if ($fieldValue instanceof MultiValueFormField) {
            if ($this->multiValueIsDisjunctive()) {
                $result = false;
                foreach ($fieldValue as $key => $value) {
                    $result = $result || $this->evalValue($value, $context, $keysEvaluated);
                }
            } else {
                $result = true;
                foreach ($fieldValue as $key => $value) {
                    $result = $result && $this->evalValue($value, $context, $keysEvaluated);
                }
            }
        } else {
            $result = $this->evalValue($fieldValue, $context, $keysEvaluated);
        }
        return $result;
    }
}
