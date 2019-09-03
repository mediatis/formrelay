<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

interface ContentResolverInterface
{
    public function build(array &$context): string;
    public function finish(array &$context, string &$result): bool;
}
