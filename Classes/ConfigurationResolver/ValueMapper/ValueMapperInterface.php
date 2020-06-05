<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolverInterface;
use Mediatis\Formrelay\Domain\Model\FormField\FormFieldInterface;

interface ValueMapperInterface extends ConfigurationResolverInterface
{
    /**
     * @param array $context
     * @param string|FormFieldInterface
     * @return string|FormFieldInterface|null
     */
    public function resolve(array $context, $fieldValue = null);
}
