<?php
namespace Mediatis\Formrelay\DataProvider;

class AdwordCampains implements \Mediatis\Formrelay\DataProviderInterface
{
    public function addData(&$dataArray)
    {
        /*
        utmz Cookie -- result form search
         */
        $utmz = new \Mediatis\Formrelay\Utility\UtmzCookieParser();
        if ($utmz) {
            // track Google Analytics source
            if ($utmz->utmz_source) {
                $dataArray['utmz_source'] = $utmz->utmz_source;
            }

            // track Google Analytics medium
            if ($utmz->utmz_medium != "") {
                $dataArray['utmz_medium'] = $utmz->utmz_medium;
            }

            // track Google Analytics campaign
            if ($utmz->utmz_campaign) {
                $dataArray['utmz_campaign'] = $utmz->utmz_campaign;
            }

            // track Google Analytics term
            if ($utmz->utmz_term) {
                $dataArray['utmz_term'] = $utmz->utmz_term;
            }

            // track Google Analytics content
            if ($utmz->utmz_content) {
                $dataArray['utmz_content'] = $utmz->utmz_content;
            }
        }

        /*
        utm cookies -- from campains
         */
        // track Google Analytics source
        if ($_COOKIE['ga_utm_source'] != "") {
            $dataArray['utm_source'] = $_COOKIE['ga_utm_source'];
        }

        // track Google Analytics medium
        if ($_COOKIE['ga_utm_medium'] != "") {
            $dataArray['utm_medium'] = $_COOKIE['ga_utm_medium'];
        }

        // track Google Analytics campaign
        if ($_COOKIE['ga_utm_campaign'] != "") {
            $dataArray['utm_campaign'] = $_COOKIE['ga_utm_campaign'];
        }

        // track Google Analytics term
        if ($_COOKIE['ga_utm_term'] != "") {
            $dataArray['utm_term'] = $_COOKIE['ga_utm_term'];
        }

        // track Google Analytics content
        if ($_COOKIE['ga_utm_content'] != "") {
            $dataArray['utm_content'] = $_COOKIE['ga_utm_content'];
        }
    }
}
