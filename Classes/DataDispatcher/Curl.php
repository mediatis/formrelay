<?php
namespace Mediatis\Formrelay\DataDispatcher;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Curl implements \Mediatis\Formrelay\DataDispatcherInterface
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function send($data)
    {

        $retval = true;

        $params = array();
        foreach ($data as $key => $value) {
            $params[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $queryString = implode('&', $params);

        $curlOptions = array(
            CURLOPT_URL => $this->url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $queryString,

            CURLOPT_REFERER => $_SERVER['HTTP_REFERER'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_MAXREDIRS => 10,
        );

        $handle = curl_init();

        curl_setopt_array($handle, $curlOptions);

        $result = curl_exec($handle);

        if ($result === false){
            $retval = false;
        }

        curl_close($handle);

        return $retval;
    }
}
