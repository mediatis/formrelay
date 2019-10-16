<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class RawValueMapper extends ValueMapper
{
    /**
     * @param array $context
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context)
    {
        $value = $context['data'][$context['key']];
        if (isset($this->config[$value])) {
            $valueMapper = $this->resolveKeyword('general', $this->config[$value]);
            $result = $valueMapper->resolve($context);
            if ($result !== null) {
                return $result;
            }
        }
        return parent::resolve($context);
    }
}
