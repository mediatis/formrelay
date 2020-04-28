<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\GeneralConfigurationResolverInterface;
use Mediatis\Formrelay\Utility\FormrelayUtility;

class GeneralContentResolver extends ContentResolver implements GeneralConfigurationResolverInterface
{
    protected function add($context, $result, $content): string
    {
        $glue = FormrelayUtility::parseSeparatorString($context[static::KEYWORD_GLUE] ?: '');
        return $result
            . ($content && $result && $glue ? $glue : '')
            . $content;
    }

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
        $config = $this->preprocessConfigurationArray(['plain', static::KEYWORD_GLUE], ['trim']);
        foreach ($config as $key => $value) {
            if ($key === static::KEYWORD_GLUE) {
                $context[static::KEYWORD_GLUE] = $value;
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
