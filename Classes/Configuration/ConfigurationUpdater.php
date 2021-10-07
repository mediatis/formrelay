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

    protected function updateDefaultsConfiguration(array &$routeConfiguration)
    {
        if (isset($routeConfiguration['defaults'])) {
            foreach ($routeConfiguration['defaults'] as $key => $value) {
                if (!array_key_exists('default', $routeConfiguration['fields'][$key] ?? [])) {
                    $routeConfiguration['fields'][$key]['default'] = $value;
                }
            }
            unset($routeConfiguration['defaults']);
        }
    }

    public function updateRouteConfiguration(string $routeName, array &$routeConfiguration)
    {
        $this->updatePassConfiguration($routeConfiguration);
        $this->updateDefaultsConfiguration($routeConfiguration);
    }
}
