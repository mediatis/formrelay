<?php

namespace Mediatis\Formrelay\Tests\Unit\DataDispatcher;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Mediatis\Formrelay\DataDispatcher\RequestDispatcher;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValueDiscrete;
use Mediatis\Formrelay\Exceptions\InvalidUrlException;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class RequestDispatcherTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructorThrowsExceptionOnInvalidUrl()
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('Bad URL invalidurl.de');
        new RequestDispatcher('invalidurl.de');
    }

    /**
     * @test
     */
    public function clientSendsRequests()
    {
        $postFields = [
            'name' => 'Max',
            'lastname' => 'Mustermann',
            'multifieldDiscrete' => new FormFieldMultiValueDiscrete(['a', 'b', 'c']),
            'multifield' => new FormFieldMultiValue(['a', 'b', 'c'])
        ];
        $testCookies = ['cookiename' => 'cookievalue'];
        $host = 'https://www.example.com';
        foreach ($testCookies as $cKey => $cValue) {
            // Set up a cookie - name, value AND domain.
            $cookie = new SetCookie();
            $cookie->setName($cKey);
            $cookie->setValue(rawurlencode($cValue));
            $cookie->setDomain(parse_url('https://www.example.com', PHP_URL_HOST));
            $cookies[] = $cookie;
        }

        $jar = new CookieJar(false, $cookies);
        /** @var RequestFactory $requestFactoryMock */
        $requestFactoryMock = $this->getMockBuilder(RequestFactory::class)->getMock();
        $requestFactoryMock->expects(
            $this->once()
        )->method('request')->with(
            'POST',
            $host,
            [
                'body' => 'name=Max&lastname=Mustermann&multifieldDiscrete=a&multifieldDiscrete=b&multifieldDiscrete=c&multifield=a%2Cb%2Cc',
                'cookies' => $jar
            ]
        )->willReturn(new \GuzzleHttp\Psr7\Response());

        $subject = new RequestDispatcher($host, $testCookies);
        ObjectAccess::setProperty($subject, 'requestFactory', $requestFactoryMock, true);
        $this->assertEquals(true, $subject->send($postFields));
    }
}
