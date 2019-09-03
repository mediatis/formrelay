<?php

namespace Mediatis\Formrelay\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;

class IfContentResolver extends ContentResolver
{
    public function finish(array &$context, string &$result): bool
    {
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $this->config);
        $evalResult = $evaluation->resolve($context);
        if ($evalResult !== null) {
            $contentResolver = $this->objectManager->get(GeneralContentResolver::class, $evalResult);
            $result = $contentResolver->resolve($context);
            return true;
        }
        return false;
    }
}
