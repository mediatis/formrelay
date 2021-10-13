<?php

namespace Mediatis\Formrelay\Configuration;

interface RouteConfigurationUpdaterInterface
{
    const SIGNAL_UPDATE_ROUTE_CONFIGURATION = 'updateRouteConfiguration';

    public function updateRouteConfiguration(string $routeName, array &$routeConfiguration);
}
