<?php
namespace Mediatis\Formrelay\DataDispatcher;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Mediatis\Formrelay\Domain\Model;

class Curl implements \Mediatis\Formrelay\DataDispatcherInterface
{
    protected $url;
    protected $options;
    protected $parameterise = true;

    public function __construct($url, $options = false)
    {
        $this->url = $url;
        $this->options = $options;
    }

    public function setParameterise($value = true)
    {
        $this->parameterise = $value;
    }

    public function send($data)
    {
        $retval = true;

        // GeneralUtility::devLog('Mediatis\\Formrelay\\DataDispatcher\\Curl::send()', __CLASS__, 0, $data);

        $postFields = $data;
        if ($this->parameterise) {
            $params = array();
            foreach ($data as $key => $value) {
                if ($value instanceof FormFieldMultiValueDiscrete) {
                    foreach ($value as $multiValue) {
                        $params[] = rawurlencode($key) . '=' . rawurlencode($multiValue);
                    }
                } else {
                    $params[] = rawurlencode($key) . '=' . rawurlencode($value);
                }
            }
            $postFields = implode('&', $params);
        }

        $curlOptions = array(
            CURLOPT_URL => $this->url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,

            CURLOPT_REFERER => $_SERVER['HTTP_REFERER'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_MAXREDIRS => 10,
        );

        if ($this->options) {
            foreach ($this->options as $key => $value) {
                if ($value === null) {
                    if (isset($curlOptions[$key])) {
                        unset($curlOptions[$key]);
                    }
                } else {
                    if ($key === CURLOPT_COOKIE) {
                        $cookieStringArray = array();
                        foreach ($value as $ckey => $cvalue) {
                            $cookieStringArray[] = $ckey . '=' . rawurlencode($cvalue);
                        }
                        $curlOptions[$key] = implode('; ', $cookieStringArray);
                    } else {
                        $curlOptions[$key] = $value;
                    }
                }
            }
        }

        GeneralUtility::devLog('Mediatis\\Formrelay\\DataDispatcher\\Curl::send()', __CLASS__, 0, $curlOptions);

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
