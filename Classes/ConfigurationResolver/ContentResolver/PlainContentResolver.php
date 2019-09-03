<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

class PlainContentResolver extends ContentResolver
{
    public function build(array &$context): string
    {
        return $this->config;
    }
}
