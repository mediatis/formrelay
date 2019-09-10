<?php

namespace Mediatis\Formrelay\DataProvider;

class Timestamp implements DataProviderInterface
{
    public function addData(array &$dataArray)
    {
        $dataArray['timestamp'] = date('c');
    }
}
