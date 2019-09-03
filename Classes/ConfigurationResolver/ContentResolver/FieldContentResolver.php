<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

class FieldContentResolver extends ContentResolver
{
    public function build(array &$context): string
    {
        if (isset($context['data'][$this->config])) {
            return $context['data'][$this->config];
        }
        return '';
    }
}
