<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class NegateFieldMapper extends FieldMapper
{
    protected function ignoreScalarConfig()
    {
        return true;
    }

    public function prepare(&$context, &$result)
    {
        // @TODO check for MultiValueFormField here and apply the negation on each value if necessary
        $context['value'] = !!$context['value']
            ? ($this->config['false'] ?: '0')
            : ($this->config['true'] ?: '1');
    }
}
