<?php

namespace Mediatis\Formrelay\Scheduler;

use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;

abstract class QueueFieldProvider implements AdditionalFieldProviderInterface
{
    protected function addField(array &$additionalFields, array $taskInfo, $fieldName, $label)
    {
        $fieldId = 'task_' . $fieldName;
        $fieldCode = '<input type="text"'
            . ' name="tx_scheduler[' . $fieldName . ']"'
            . ' id="' . $fieldId . '"'
            . ' value="' . $taskInfo[$fieldName] . '"'
            . ' size="30" />';
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => $label,
        ];
    }

    protected function addCheckboxField(array &$additionalFields, array $taskInfo, $fieldName, $label)
    {
        $fieldId = 'task_' . $fieldName;
        $fieldCode = '<input type="checkbox"'
            . ' name="tx_scheduler[' . $fieldName . ']"'
            . ' id="' . $fieldId . '"'
            . ($taskInfo[$fieldName] ? ' checked="checked"' : '')
            . ' value="1" />';
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => $label,
        ];
    }
}
