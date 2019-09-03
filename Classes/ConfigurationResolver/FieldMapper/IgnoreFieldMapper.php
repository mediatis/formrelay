<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class IgnoreFieldMapper extends FieldMapper
{
    public function finish(&$context, &$result)
    {
        return !!$this->config;
    }
}
