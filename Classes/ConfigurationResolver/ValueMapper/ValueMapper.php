<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;
use Mediatis\Formrelay\Service\Registerable;

abstract class ValueMapper extends ConfigurationResolver implements ValueMapperInterface, Registerable
{
    protected function getResolverClass(): string
    {
        return ValueMapper::class;
    }

    /**
     * @param array $context
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context)
    {
        return $context['data'][$context['key']];
    }
}
