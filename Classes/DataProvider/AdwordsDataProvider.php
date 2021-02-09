<?php

namespace Mediatis\Formrelay\DataProvider;

use FormRelay\Core\DataProvider\DataProvider;
use FormRelay\Core\Model\Submission\SubmissionInterface;

class AdwordsDataProvider extends DataProvider
{
    const COOKIE_KEYWORDS = 'adwords_keywords';
    const COOKIE_EVENTCODE = 'adwords_eventcode';

    protected function processContext(SubmissionInterface $submission)
    {
        $this->addCookieToContext($submission, static::COOKIE_KEYWORDS);
        $this->addCookieToContext($submission, static::COOKIE_EVENTCODE);
    }

    protected function process(SubmissionInterface $submission)
    {
        // track LMS Keywords
        $this->setFieldFromCookie($submission, static::COOKIE_KEYWORDS);

        // track LMS Eventcode
        $this->setFieldFromCookie($submission, static::COOKIE_EVENTCODE);
    }
}
