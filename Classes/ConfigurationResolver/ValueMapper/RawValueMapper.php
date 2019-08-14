<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class RawValueMapper extends GeneralValueMapper
{
    protected function resolveKeyword(string $keyword, $config)
    {
        return null;
    }
}
