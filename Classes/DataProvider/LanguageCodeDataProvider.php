<?php

namespace Mediatis\Formrelay\DataProvider;

use FormRelay\Core\DataProvider\DataProvider;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Request\RequestInterface;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class LanguageCodeDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = 'language';

    protected function processContext(SubmissionInterface $submission, RequestInterface $request)
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(
            VersionNumberUtility::getNumericTypo3Version()
        ) >= 10000000) {
            $language = $GLOBALS['TSFE']->getLanguage()->getTwoLetterIsoCode();
        } else {
            $language = $GLOBALS['TSFE']->sys_language_isocode;
        }
        $this->addToContext($submission, 'language', $language);
    }

    protected function process(SubmissionInterface $submission)
    {
        $this->setFieldFromContext(
            $submission,
            'language',
            $this->getConfig(static::KEY_FIELD, static::DEFAULT_FIELD)
        );
    }
}
