<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;


use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class GeneralContentResolver extends ContentResolver
{
    /*
     * sender = foo@bar.de
     *
     * sender = {email}, foo@bar.de
     * sender.insertData = 1
     *
     * sender.field = email
     *
     * sender {
     *   10 = Hello
     *   20.field = email
     * }
     *
     * sender {
     *   config {
     *     delimiter = \s
     *   }
     *   10 = Hello
     *   20.field = email
     * }
     *
     * sender {
     *   10 = Hello
     *   20.field = email
     * }
     *
     * sender = Hello
     * sender.if {
     *   language = es
     *   then = Hola
     * }
     *
     * sender {
     *   10 = Hello
     *   10.if {
     *     language = es
     *     then Hola
     *   }
     *   20.field = name
     * }
     */

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
