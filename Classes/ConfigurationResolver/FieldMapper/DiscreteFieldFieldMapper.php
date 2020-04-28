<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Domain\Model\FormField\DiscreteMultiValueFormField;

class DiscreteFieldFieldMapper extends FieldMapper
{

    protected function finishValue($fieldValue, &$context, &$result)
    {
        $result[$context['mappedKey']]->append($fieldValue);
    }

    public function finish(&$context, &$result): bool
    {
        if (!isset($result[$context['mappedKey']])) {
            // if not set yet, create a discrete multi-value field
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([]);
        } elseif ($result[$context['mappedKey']] instanceof MultiValueFormField) {
            // if already a multi-value field, transfer to a discrete one
            $values = $result[$context['mappedKey']];
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([]);
            foreach ($values as $value) {
                $result[$context['mappedKey']]->append($value);
            }
        } else {
            // if already set with some other value, insert it to a new discrete multi-value field
            $result[$context['mappedKey']] = new DiscreteMultiValueFormField([$result[$context['mappedKey']]]);
        }

        parent::finish($context, $result);
        return true;
    }
}
