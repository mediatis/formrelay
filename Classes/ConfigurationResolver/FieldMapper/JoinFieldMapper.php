<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Utility\FormrelayUtility;

class JoinFieldMapper extends FieldMapper
{
    public function prepare(&$result, &$context)
    {
        if ($context['value'] instanceof FormFieldMultiValue) {
            $glue = FormrelayUtility::parseSeparatorString($this->config['glue'] ?: '\\n');
            $context['value'] = implode($glue, iterator_to_array($context['value']));
        }
    }
}
