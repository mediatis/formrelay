<?php

namespace Mediatis\Formrelay\Configuration;

use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager as OriginalFrontendConfigurationManager;

class FrontendConfigurationManager extends OriginalFrontendConfigurationManager implements FrontendConfigurationManagerInterface
{
    // TODO for typo3 9 we need to overwrite this method
    //      to match the signature of the interface FrontendConfigurationManagerInterface
    public function getTypoScriptSetup(): array
    {
        return parent::getTypoScriptSetup();
    }
}
