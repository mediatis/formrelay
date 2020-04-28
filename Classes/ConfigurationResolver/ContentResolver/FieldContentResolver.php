<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

class FieldContentResolver extends ContentResolver
{
    public function build(array &$context): string
    {
        return $this->getFieldValue($context, $this->config);
    }
}
