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

    public function eval(array $context = [], array $keysEvaluated = []): bool
    {
        $result = $this->initialValue();
        $config = $this->preprocessConfigurationArray();
        foreach ($config as $key => $value) {

            if ($key === 'field' && !is_array($value)) {
                $context['key'] = $value;
                continue;
            }

            $evaluation = $this->resolveKeyword($key, $value);

            if (!$evaluation) {
                if (is_numeric($key)) {
                    $evaluation = $this->resolveKeyword('general', $value);
                } else {
                    $context['key'] = $key;
                    if (is_array($value)) {
                        $evaluation = $this->resolveKeyword('general', $value);
                    } else {
                        $evaluation = $this->resolveKeyword('equals', $value);
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
