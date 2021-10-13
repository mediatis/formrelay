<?php

namespace Mediatis\Formrelay\DataProvider;

use FormRelay\Core\DataProvider\DataProvider;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Request\RequestInterface;
use Mediatis\Formrelay\Utility\UtmzCookieParser;

class AdwordsCampaignsDataProvider extends DataProvider
{
    const UTMZ_MAP = [
        'utmz_source' => 'utmz_source',
        'utmz_medium' => 'utmz_medium',
        'utmz_campaign' => 'utmz_campaign',
        'utmz_term' => 'utmz_term',
        'utmz_content' => 'utmz_content',
    ];

    const UTM_MAP = [
        'ga_utm_source' => 'utm_source',
        'ga_utm_medium' => 'utm_medium',
        'ga_utm_campaign' => 'utm_campaign',
        'ga_utm_term' => 'utm_term',
        'ga_utm_content' => 'utm_content',
    ];

    protected function processContext(SubmissionInterface $submission, RequestInterface $request)
    {
        $this->addCookieToContext($submission, $request, '__utmz');
        foreach (array_keys(static::UTM_MAP) as $cookie) {
            $this->addCookieToContext($submission, $request, $cookie);
        }
    }

    protected function process(SubmissionInterface $submission)
    {
        $cookies = $submission->getContext()->getCookies();
        $utmz = new UtmzCookieParser($cookies);
        foreach (static::UTMZ_MAP as $member => $field) {
            if ($utmz->$member) {
                $this->setField($submission, $field, $utmz->$member);
            }
        }

        foreach (static::UTM_MAP as $cookie => $field) {
            if (isset($cookies[$cookie])) {
                $this->setField($submission, $field, $cookies[$cookie]);
            }
        }
    }
}
