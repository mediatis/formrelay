<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class InsertDataContentResolver extends ContentResolver
{
    public function finish(array &$context, string &$result): bool
    {
        if ($this->config) {
            foreach ($context['data'] as $key => $value) {
                $result = str_replace('{' . $key . '}', $value, $result);
            }
            $result = FormrelayUtility::parseSeparatorString($result);
            $result = preg_replace('/\\{[-_a-zA-Z0-9]+\\}/', '', $result);
        }
        return false;
    }
}
