<?php

namespace Mediatis\Formrelay\ExpressionLanguage;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TypoScriptFormrelay
{
    public function extensionLoaded($extKey)
    {
        return ExtensionManagementUtility::isLoaded($extKey);
    }
}
