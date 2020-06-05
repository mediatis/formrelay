<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\Utility\FormrelayUtility;

class InsertDataContentResolver extends ContentResolver
{
    public function finish(array &$context, string &$result): bool
    {
        if ($this->config) {
            foreach (array_keys($context['data']) as $key) {
                $result = str_replace('{' . $key . '}', $this->getFieldValue($context, $key), $result);
            }
            $result = FormrelayUtility::parseSeparatorString($result);
            $result = preg_replace('/\\{[-_a-zA-Z0-9]+\\}/', '', $result);
        }
        return false;
    }
}
