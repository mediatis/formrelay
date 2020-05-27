<?php

namespace Mediatis\Formrelay\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            'formrelay' => GeneralUtility::makeInstance(TypoScriptFormrelay::class),
        ];
    }
}
