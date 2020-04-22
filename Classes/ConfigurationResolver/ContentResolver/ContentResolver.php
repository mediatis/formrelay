<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolver;
use Mediatis\Formrelay\Service\Registerable;

abstract class ContentResolver extends ConfigurationResolver implements ContentResolverInterface, Registerable
{

    protected function getResolverClass(): string
    {
        return ContentResolver::class;
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
