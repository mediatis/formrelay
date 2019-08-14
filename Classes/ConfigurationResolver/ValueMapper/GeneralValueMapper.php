<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

class GeneralValueMapper extends ValueMapper
{

    public function process($context)
    {
        if (!is_array($this->config)) {
            return $this->config;
        } elseif (!empty($this->config)) {
            foreach ($this->config as $key => $value) {

                // try to instantiate sub-mapper
                $valueMapper = $this->resolveKeyword($key, $value);

                // if not successful, create a general mapper as sub-mapper if the config key is the data value
                if (!$valueMapper && $key === $context['data'][$context['key']]) {
                    $valueMapper = $this->objectManager->get(GeneralValueMapper::class, $value);
                }

                // calculate the result
                $result = false;
                if ($valueMapper) {
                    $result = $valueMapper->process($context);
                }

                // if the result is not a boolean (may be returned from an evaluation process without a then/else part)
                // then stop and return the result
                if (gettype($result) !== 'boolean') {
                    return $result;
                }

            }
        }
        // if no result was found, return the original value
        return $context['data'][$context['key']];
    }

}
