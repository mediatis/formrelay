<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Domain\Model\FormField\DiscreteMultiValueFormField;

class DiscreteFieldFieldMapper extends FieldMapper
{

    public function finish(&$context, &$result): bool
    {
        if (!isset($result[$context['mappedKey']])) {
            // if not set yet, create a discrete multi-value field
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([]);
        } elseif ($result[$context['mappedKey']] instanceof MultiValueFormField) {
            // if already a (non-discrete) multi-value field, transfer to a discrete one
            $values = $result[$context['mappedKey']];
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([]);
            foreach ($values as $value) {
                $result[$context['mappedKey']]->append($value);
            }
        } elseif (!($result[$context['mappedKey']] instanceof DiscreteMultiValueFormField)) {
            // if already set with some other value, insert it to a new discrete multi-value field
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([$result[$context['mappedKey']]]);
        }

        if ($context['value'] instanceof MultiValueFormField) {
            // if the value is a multi-value, append each value
            foreach ($context['value'] as $mappedMultiValue) {
                $result[$context['mappedKey']]->append($mappedMultiValue);
            }
        } else {
            // else append just the value in a whole
            $result[$context['mappedKey']]->append($context['value']);
        }
        return true;
    }
}
