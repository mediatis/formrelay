<?php

namespace Mediatis\Formrelay\Configuration;

interface BaseConfigurationUpdaterInterface
{
    const SIGNAL_UPDATE_BASE_CONFIGURATION = 'updateBaseConfiguration';

    public function updateBaseConfiguration(string $routeName, array &$routeConfiguration);
}
