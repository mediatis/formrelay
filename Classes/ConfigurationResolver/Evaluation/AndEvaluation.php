<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

class AndEvaluation extends Evaluation
{

    protected function initialValue()
    {
        return true;
    }

    protected function calculate($result, $evaluation, $context, $keysEvaluated)
    {
        return $result && $evaluation->eval($context, $keysEvaluated);
    }

    public function eval(array $context = [], array $keysEvaluated = [])
    {
        $result = $this->initialValue();
        foreach ($this->config as $key => $value) {
            $evaluation = $this->resolveKeyword($key, $value);
            if (!$evaluation) {
                if (is_numeric($key)) {
                    // '10' => $subConfig
                    $evaluation = $this->objectManager->get(GeneralEvaluation::class, $value);
                } elseif ($key === 'field' && !is_array($value)) {
                    // 'field' => field_name
                    $context['key'] = $value;
                } else {
                    $context['key'] = $key;
                    if (is_array($value)) {
                        // 'field_name' => $subConfig
                        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $value);
                    } else {
                        // 'field_name' => 'some_value'
                        $evaluation = $this->objectManager->get(EqualsEvaluation::class, $value);
                    }
                }
            }
            if ($evaluation) {
                $result = $this->calculate($result, $evaluation, $context, $keysEvaluated);
            }
        }
        return $result;
    }
}
