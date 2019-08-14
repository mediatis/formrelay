<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class RawFieldMapper extends GeneralFieldMapper
{
    protected function resolveKeyword(string $keyword, $config)
    {
        return null;
    }
}
