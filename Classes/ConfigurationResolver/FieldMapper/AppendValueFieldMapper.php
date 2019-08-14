<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class AppendValueFieldMapper extends FieldMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function finish(&$result, &$context)
    {
        $separator = FormrelayUtility::parseSeparatorString($this->config['separator'] ?: '\\n');
        if (!isset($result[$context['mappedKey']])) {
            $result[$context['mappedKey']] = '';
        }
        $result[$context['mappedKey']] .= $context['value'] . $separator;
        return true;
    }
}
