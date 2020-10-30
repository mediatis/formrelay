<?php

namespace Mediatis\Formrelay\DataDispatcher;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use Mediatis\Formrelay\Domain\Model\FormField\DiscreteMultiValueFormField;
use Mediatis\Formrelay\Exceptions\InvalidUrlException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class RequestDispatcher implements DataDispatcherInterface
{
    const DEFAULT_HEADERS = [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Accept' => '*/*',
    ];

    /** @var ObjectManager */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    /** @var RequestFactory */
    protected $requestFactory;

    protected $url;
    protected $method = 'POST';
    protected $cookies = [];
    protected $headers = [];
    protected $parameterise = true;

    public function initializeObject()
    {
        $logManager = $this->objectManager->get(LogManager::class);
        $this->logger = $logManager->getLogger(static::class);
    }

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

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
     * @param array $headers
     * @throws InvalidUrlException
     */
    public function __construct($url, $cookies = [], $headers = [])
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            throw new InvalidUrlException($url);
        }
        $this->url = $url;
        $this->cookies = $cookies;
        $this->headers = $headers;
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
        // body
        $params = $this->parameterize($data);
        $requestBody = implode('&', $params);

        // headers
        $requestHeaders = static::DEFAULT_HEADERS;
        foreach ($this->headers as $key => $value) {
            if ($value === null) {
                unset($requestHeaders[$key]);
            } else {
                $requestHeaders[$key] = $value;
            }
        }

        // cookies
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
        $cookieJar = new CookieJar(false, $requestCookies);

        $requestOptions = [
            'body' => $requestBody,
            'cookies' => $cookieJar,
            'headers' => $requestHeaders,
        ];

        try {
            $this->requestFactory->request($this->url, $this->method, $requestOptions);
        } catch (GuzzleException $e) {
            $this->logger->error('GuzzleException: "' . $e->getMessage() . '"', ['exception' => $e]);
            return false;
        }
        return true;
    }

}
