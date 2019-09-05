<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;
use Mediatis\Formrelay\Utility\FormrelayUtility;

abstract class ContentResolver extends ConfigurationResolver implements ContentResolverInterface, Registerable
{

    protected function getResolverClass(): string
    {
        return ContentResolver::class;
    }

    protected function add($context, $result, $content): string
    {
        $delimiter = FormrelayUtility::parseSeparatorString($context['delimiter'] ?: '');
        return $result
            . ($content && $result && $delimiter ? $delimiter : '')
            . $content;
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
