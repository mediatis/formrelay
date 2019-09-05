<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\GeneralConfigurationResolverInterface;

class GeneralContentResolver extends ContentResolver implements GeneralConfigurationResolverInterface
{
    public function resolve(array $context): string
    {
        $result = $this->build($context);
        $this->finish($context, $result);
        return $result;
    }

    public function build(array &$context): string
    {
        $result = '';
        $contentResolvers = [];
        $config = $this->preprocessConfigurationArray(['plain']);
        foreach ($config as $key => $value) {
            if ($key === 'delimiter') {
                $context['delimiter'] = $value;
                continue;
            }
            $contentResolver = $this->resolveKeyword(is_numeric($key) ? 'general' : $key, $value);
            if ($contentResolver) {
                $contentResolvers[] = $contentResolver;
                $content = $contentResolver->build($context);
                $result = $this->add($context, $result, $content);
            }
        }
        foreach ($contentResolvers as $contentResolver) {
            if ($contentResolver->finish($context, $result)) {
                break;
            }
        }
        return $result;
    }
}
