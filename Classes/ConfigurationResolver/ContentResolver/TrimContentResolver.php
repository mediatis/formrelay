<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

class TrimContentResolver extends ContentResolver
{
    public function finish(array &$context, string &$result): bool
    {
        if ($this->config) {
            $result = trim($result);
        }
        return false;
    }
}
