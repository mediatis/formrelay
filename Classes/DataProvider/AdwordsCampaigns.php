<?php

namespace Mediatis\Formrelay\DataProvider;

use Mediatis\Formrelay\Utility\UtmzCookieParser;

class AdwordsCampaigns implements DataProviderInterface
{
    protected $utmzMap = [
        'utmz_source' => 'utmz_source',
        'utmz_medium' => 'utmz_medium',
        'utmz_campaign' => 'utmz_campaign',
        'utmz_term' => 'utmz_term',
        'utmz_content' => 'utmz_content',
    ];

    protected $utmMap = [
        'ga_utm_source' => 'utm_source',
        'ga_utm_medium' => 'utm_medium',
        'ga_utm_campaign' => 'utm_campaign',
        'ga_utm_term' => 'utm_term',
        'ga_utm_content' => 'utm_content',
    ];

    public function addData(array &$dataArray)
    {
        // utmz cookie -- result form search
        $utmz = new UtmzCookieParser();
        if ($utmz) {
            foreach ($this->utmzMap as $member => $field) {
                if ($utmz->$member) {
                    $dataArray[$field] = $utmz->$member;
                }
            }
        }

        // utm cookies -- from campaigns
        foreach ($this->utmMap as $cookie => $field) {
            if ($_COOKIE[$cookie] != '') {
                $dataArray[$field] = $_COOKIE[$cookie];
            }
        }
    }
}
