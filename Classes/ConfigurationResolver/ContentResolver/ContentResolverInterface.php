<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ConfigurationResolverInterface;

interface ContentResolverInterface extends ConfigurationResolverInterface
{
    public function build(array &$context): string;
    public function finish(array &$context, string &$result): bool;
}
