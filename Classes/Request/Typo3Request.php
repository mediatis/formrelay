<?php

namespace Mediatis\Formrelay\Request;

use FormRelay\Core\Request\DefaultRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Typo3Request extends DefaultRequest
{
    public function getRequestVariable(string $name): string
    {
        return GeneralUtility::getIndpEnv($name);
    }
}
