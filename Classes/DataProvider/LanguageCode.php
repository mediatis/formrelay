<?php

namespace Mediatis\Formrelay\DataProvider;

class LanguageCode implements DataProviderInterface
{
    public function addData(array &$dataArray)
    {
        $dataArray['language'] = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
    }
}
