<?php

namespace Mediatis\Formrelay\DataProvider;

class IpAddress implements DataProviderInterface
{
    public function addData(array &$dataArray)
    {
        $dataArray['ip_address'] = \Mediatis\Formrelay\Utility\IpAddress::getUserIpAdress();
    }
}
