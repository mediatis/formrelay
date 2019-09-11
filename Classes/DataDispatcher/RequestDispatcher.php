<?php

namespace Mediatis\Formrelay\DataDispatcher;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use Mediatis\Formrelay\Domain\Model\FormField\DiscreteMultiValueFormField;
use Mediatis\Formrelay\Exceptions\InvalidUrlException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestDispatcher implements DataDispatcherInterface
{
    protected $url;
    protected $method = 'POST';
    protected $cookies = [];
    protected $parameterise = true;
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @param RequestFactory $requestFactory
     */
    public function injectRequestFactory(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param $url
     * @param array $cookies
     * @throws InvalidUrlException
     */
    public function __construct($url, $cookies = [])
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            throw new InvalidUrlException($url);
        }
        $this->url = $url;
        $this->cookies = $cookies;
    }

    /**
     * urlencode data and parse fields of type FormFieldMultiValueDiscrete
     * @param array formData $data
     * @return array
     */
    protected function parameterize($data)
    {
        $params = [];
        foreach ($data as $key => $value) {
            if ($value instanceof DiscreteMultiValueFormField) {
                foreach ($value as $multiValue) {
                    $params[] = rawurlencode($key) . '=' . rawurlencode($multiValue);
                }
            } else {
                $params[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        return $params;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function send(array $data): bool
    {
        $params = $this->parameterize($data);

        $postFields = implode('&', $params);

        $requestCookies = [];
        if (!empty($this->cookies)) {
            $host = parse_url($this->url, PHP_URL_HOST);
            foreach ($this->cookies as $cKey => $cValue) {
                // Set up a cookie - name, value AND domain.
                $cookie = new SetCookie();
                $cookie->setName($cKey);
                $cookie->setValue(rawurlencode($cValue));
                $cookie->setDomain($host);
                $requestCookies[] = $cookie;
            }
        }
        $jar = new CookieJar(false, $requestCookies);

        try {
            $this->requestFactory->request($this->method, $this->url, ['body' => $postFields, 'cookies' => $jar]);
        } catch (GuzzleException $e) {
            GeneralUtility::devLog($e->getMessage(), __CLASS__);
            return false;
        }
        return true;
    }

}
