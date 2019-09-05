<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class SplitFieldMapper extends FieldMapper
{

    public function finish(&$context, &$result): bool
    {
        $token = FormrelayUtility::parseSeparatorString($this->config['token'] ?: '\\s');
        $splitFields = $this->config['fields'];
        $splitValues = explode($token, $context['value']);
        while (count($splitFields) > 1 && count($splitValues) > 0) {
            // split for all fields but the last
            $splitField = array_shift($splitFields);
            $splitValue = array_shift($splitValues);
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $splitField);
            $fieldMapper->resolve($result, [
                'data' => $context['data'],
                'key' => $context['key'],
                'value' => $splitValue,
            ]);
        }
        if (count($splitValues) > 0) {
            // concat the remaining split values again and use them for the last field
            $splitField = array_shift($splitFields);
            $splitValue = implode($token, $splitValues);
            $fieldMapper = $this->objectManager->get(GeneralFieldMapper::class, $splitField);
            $fieldMapper->resolve($result, [
                'data' => $context['data'],
                'key' => $context['key'],
                'value' => $splitValue,
            ]);
        }
        return true;
    }
}
