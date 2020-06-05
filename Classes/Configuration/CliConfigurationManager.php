<?php

namespace Mediatis\Formrelay\Configuration;

class CliConfigurationManager implements FrontendConfigurationManagerInterface
{
    protected $typoScriptSetup = [];

    public function getTypoScriptSetup(): array
    {
        return $this->typoScriptSetup;
    }

    public function setTypoScriptSetup(array $typoScriptSetup)
    {
        $this->typoScriptSetup = $typoScriptSetup;
    }
}
