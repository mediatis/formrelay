<?php

namespace Mediatis\Formrelay\DataProvider;

class Timestamp implements \Mediatis\Formrelay\DataProviderInterface
{
    public function addData(&$dataArray)
    {
        $dataArray['timestamp'] = date('d M Y g:i A');
    }
}
