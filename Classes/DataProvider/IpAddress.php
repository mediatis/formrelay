<?php
namespace Mediatis\Formrelay\DataProvider;

class IpAddress implements \Mediatis\Formrelay\DataProviderInterface
{
    public function addData(&$dataArray)
    {
        $dataArray['ipAddress'] =  \Mediatis\Formrelay\Utility\IpAddress::getUserIpAdress();
    }
}
