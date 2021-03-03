<?php

namespace Mediatis\Formrelay\Configuration;

class ConfigurationUpdater implements RouteConfigurationUpdaterInterface
{
    protected function updatePassConfiguration(array &$routeConfiguration)
    {
        $keys = array_keys($routeConfiguration);
        foreach ($keys as $key) {
            if (is_numeric($key)) {
                if (!array_key_exists($key, $routeConfiguration['passes'] ?? [])) {
                    $routeConfiguration['passes'][$key] = $routeConfiguration[$key];
                }
                unset($routeConfiguration[$key]);
            }
        }
    }

    public function updateRouteConfiguration(string $routeName, array &$routeConfiguration)
    {
        $this->updatePassConfiguration($routeConfiguration);
    }
}
