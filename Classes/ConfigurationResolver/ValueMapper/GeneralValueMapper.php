<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\GeneralConfigurationResolverInterface;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

class GeneralValueMapper extends ValueMapper implements GeneralConfigurationResolverInterface
{
    /**
     * @param array $context
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context)
    {
        $config = $this->preprocessConfigurationArray(['if'], ['plain']);
        foreach ($config as $key => $value) {
            // try to instantiate sub-mapper
            $valueMapper = $this->resolveKeyword($key, $value);

            // if not successful, create a general mapper as sub-mapper if the config key is the data value
            if (
                !$valueMapper
                && isset($context['data'][$context['key']])
                && $key === $context['data'][$context['key']]
            ) {
                $valueMapper = $this->resolveKeyword('general', $value);
            }

            if ($valueMapper) {
                // calculate the result
                $result = $valueMapper->resolve($context);
                // if the result is not null (may be returned from an evaluation process without a then/else part)
                // then stop and return the result
                if ($result !== null) {
                    return $result;
                }
            }
        }
        // if no result was found, return the original value
        return parent::resolve($context);
    }
}
