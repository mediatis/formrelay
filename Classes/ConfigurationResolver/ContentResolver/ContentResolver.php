<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Service\Registerable;

abstract class ContentResolver extends ConfigurationResolver implements ContentResolverInterface, Registerable
{
    const KEYWORD_GLUE = 'glue';

    protected function getResolverClass(): string
    {
        return ContentResolver::class;
    }

    protected function getFieldValue($context, $key)
    {
        $fieldValue = isset($context['data'][$key])
            ? $context['data'][$key]
            : '';
        if ($fieldValue instanceof MultiValueFormField && isset($context[static::KEYWORD_GLUE])) {
            $fieldValue = implode($context[static::KEYWORD_GLUE], iterator_to_array($fieldValue));
        }
        return $fieldValue;
    }

    public function build(array &$context): string
    {
        return '';
    }

    public function finish(array &$context, string &$result): bool
    {
        return false;
    }
}
