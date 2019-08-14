<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValueDiscrete;

class DiscreteFieldFieldMapper extends FieldMapper
{

    public function finish(&$result, &$context)
    {
        if (!isset($result[$context['mappedKey']])) {
            // if not set yet, create a discrete multi-value field
            $result[$context['mappedKey']] = new FormFieldMultiValueDiscrete([]);
        } elseif ($result[$context['mappedKey']] instanceof FormFieldMultiValue) {
            // if already a (non-discrete) multi-value field, transfer to a discrete one
            $values = $result[$context['mappedKey']];
            $result[$context['mappedKey']] = new FormFieldMultiValueDiscrete([]);
            foreach ($values as $value) {
                $result[$context['mappedKey']]->append($value);
            }
        } elseif (!($result[$context['mappedKey']] instanceof FormFieldMultiValueDiscrete)) {
            // if already set with some other value, insert it to a new discrete multi-value field
            $result[$context['mappedKey']] = new FormFieldMultiValueDiscrete([$result[$context['mappedKey']]]);
        }

        if ($context['value'] instanceof FormFieldMultiValue) {
            // if the value is a multi-value, append each value
            foreach ($context['value'] as $mappedMultiValue) {
                $result[$context['mappedKey']]->append($mappedMultiValue);
            }
        } else {
            // else append just the value in a whole
            $result[$context['mappedKey']]->append($context['value']);
        }
    }
}
