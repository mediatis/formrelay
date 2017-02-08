<?php
namespace Mediatis\Formrelay\DataDispatcher;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Curl implements \Mediatis\Formrelay\DataDispatcherInterface
{
    protected $url;
    protected $cookies;

    public function __construct($url, $cookies = false)
    {
        $this->url = $url;
        $this->cookies = $cookies;
    }

    public function send($data)
    {
        $retval = true;

        // GeneralUtility::devLog('Mediatis\\Formrelay\\DataDispatcher\\Curl::send()', __CLASS__, 0, $data);

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

        if ($this->cookies) {
            $cookieStringArray = array();
            foreach ($this->cookies as $key => $value) {
                $cookieStringArray[] = $key . '=' . rawurlencode($value);
            }
            $curlOptions[CURLOPT_COOKIE] = implode('; ', $cookieStringArray);
        }


        $handle = curl_init();

        curl_setopt_array($handle, $curlOptions);

        $result = curl_exec($handle);

        if ($result === false) {
            GeneralUtility::devLog(curl_error($handle), __CLASS__);
            $retval = false;
        }

        curl_close($handle);

        return $retval;
    }
}
