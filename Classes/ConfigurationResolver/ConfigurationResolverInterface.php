<?php

namespace Mediatis\Formrelay\ConfigurationResolver;

interface ConfigurationResolverInterface
{
    /**
     * ConfigurationResolver constructor.
     * @param array|string $config
     */
    public function __construct($config = []);
}
