<?php

namespace Mediatis\Formrelay\DataProvider;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class LanguageCode implements DataProviderInterface
{
    public function addData(array &$dataArray)
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        ) >= 10000000) {
            $dataArray['language'] = $GLOBALS['TSFE']->getLanguage()->getTwoLetterIsoCode();
        } else {
            $dataArray['language'] = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
        }
    }
}
