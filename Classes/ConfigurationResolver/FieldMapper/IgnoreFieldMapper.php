<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class IgnoreFieldMapper extends FieldMapper
{
    public function finish(&$result, &$context)
    {
        return !!$this->config;
    }
}
