<?php

namespace Mediatis\Formrelay\DataProvider;

class Adwords implements DataProviderInterface
{
    public function addData(array &$dataArray)
    {
        // track LMS Keywords
        if ($_COOKIE['adwords_keywords'] != "") {
            $dataArray['adwords_keywords'] = $_COOKIE['adwords_keywords'];
        }
        // track LMS Eventcode
        if ($_COOKIE['adwords_eventcode'] != "") {
            $dataArray['adwords_eventcode'] = $_COOKIE['adwords_eventcode'];
        }
    }
}
