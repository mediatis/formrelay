<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class PassthroughValueMapper extends ValueMapper
{
    public function process($context)
    {
        return $context['data'][$context['key']];
    }
}
