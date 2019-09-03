<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class AppendKeyValueFieldMapper extends FieldMapper
{

    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function finish(&$context, &$result)
    {
        $keyValueSeparator = FormrelayUtility::parseSeparatorString($this->config['keyValueSeparator'] ?: '\\s=\\s');
        $separator = FormrelayUtility::parseSeparatorString($this->config['separator'] ?: '\\n');
        if (!isset($result[$context['mappedKey']])) {
            $result[$context['mappedKey']] = '';
        }
        $result[$context['mappedKey']] .= $context['key'] . $keyValueSeparator . $context['value'] . $separator;
        return true;
    }
}
