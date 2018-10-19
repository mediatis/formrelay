<?php

namespace Mediatis\Formrelay\DataProvider;

class LanguageCode implements \Mediatis\Formrelay\DataProviderInterface
{
    public function addData(&$dataArray)
    {
        $dataArray['language'] = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
    }
}
