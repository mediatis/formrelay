<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Test\Unit\Service;

use Mediatis\Formrelay\Exceptions\InvalidXmlException;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use Mediatis\Formrelay\Service\Relay;
use Mediatis\Formrelay\Service\FormSimulatorService;
use Mediatis\Formrelay\Tests\Unit\FormrelayUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class FormSimulatorServiceTest extends FormrelayUnitTestCase
{
    /** @var FormSimulatorService */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $subject = new FormSimulatorService();
        ObjectAccess::setProperty($subject, 'submitDelay', 0, true);
        $this->subject = $subject;
        $this->buildTestCaseForTsfe(24);
    }

    public function tearDown()
    {
        parent::tearDown();
        GeneralUtility::resetSingletonInstances([]);
        $this->subject = null;
    }

    /**
     * @test
     */
    public function runWithInvaliFileThrowsError()
    {
        /** @var Relay|MockObject $formrelayManager */
        $relayMock = $this->getMockBuilder(Relay::class)->setMethods(['process'])->getMock();
        $relayMock->expects($this->never())->method('process');
        $this->subject->injectRelay($relayMock);

        $this->expectException(InvalidXmlFileException::class);
        $this->subject->run(__DIR__ . 'This_file_does_not_exist', 24);
    }

    /**
     * @test
     */
    public function runWithInvalidXmlDataThrowsError()
    {
        /** @var Relay|MockObject $formrelayManager */
        $relayMock = $this->getMockBuilder(Relay::class)->setMethods(['process'])->getMock();
        $relayMock->expects($this->never())->method('process');
        $this->subject->injectRelay($relayMock);

        $this->expectException(InvalidXmlException::class);
        $this->subject->run(__DIR__ . '/../../Fixtures/invalid_log.xml', 24);
    }

    /**
     * @test
     */
    public function runReturnsResultMessageAfterNothingIsParsed()
    {
        /** @var Relay|MockObject $formrelayManager */
        $relayMock = $this->getMockBuilder(Relay::class)->setMethods(['process'])->getMock();
        $relayMock->expects($this->never())->method('process');
        $this->subject->injectRelay($relayMock);

        $this->assertEquals(
            'INFO: 0 log entries re-sent.',
            $this->subject->run(__DIR__ . '/../../Fixtures/valid_log_nothing_to_send.xml', 24)
        );
    }

    /**
     * @test
     */
    public function runParsesXmlInReversedOrderInitializesTsfeAndReturnsResultMessage()
    {
        $data = [
            [
                'salutation' => 'Mr.',
                'first_name' => 'TestFirstName2',
                'last_name' => 'TestLastName2',
                'email' => '017hgg31tgnb13@sharklasers.com',
                'company' => 'Testcompany',
                'department' => 'TestDepartment-2',
                'address_one' => 'TestAddressOne-2',
                'address_two' => 'TestAddressTwo-2',
                'zip' => 'TestZip-2',
                'city' => 'TestCity-2',
                'state' => 'TestState-2',
                'territory' => 'TestTerritory-2',
                'country' => 'TestCountry-2',
                'website' => 'www.mediatis.de',
                'phone' => '000000000000000',
                'fax' => '000000000000001',
                'job_title' => 'TestJobTitle-2',
                'years_in_business' => '66',
                'annual_revenue' => '67',
                'employees' => '68',
                'industry' => 'TestIndustry-1',
                'do_not_email' => '',
                'do_not_call' => '',
                'opted_out' => '',
                'source' => 'test_source',
                'submit' => 'submit',
                'language' => 'en',
            ],
            [
                'salutation' => 'Mrs.',
                'first_name' => 'TestFirstName2',
                'last_name' => 'TestLastName2',
                'email' => '017hgg31tgnb13@sharklasers.com',
                'company' => 'Testcompany',
                'department' => 'TestDepartment-2',
                'address_one' => 'TestAddressOne-2',
                'address_two' => 'TestAddressTwo-2',
                'zip' => 'TestZip-2',
                'city' => 'TestCity-2',
                'state' => 'TestState-2',
                'territory' => 'TestTerritory-2',
                'country' => 'TestCountry-2',
                'website' => 'www.mediatis.de',
                'phone' => '000000000000000',
                'fax' => '000000000000001',
                'job_title' => 'TestJobTitle-2',
                'years_in_business' => '66',
                'annual_revenue' => '67',
                'employees' => '68',
                'industry' => 'TestIndustry-1',
                'do_not_email' => '',
                'do_not_call' => '',
                'opted_out' => '',
                'source' => 'test_source',
                'submit' => 'submit',
                'language' => 'en',
            ]
        ];


        /** @var Relay|MockObject $formrelayManager */
        $relayMock = $this->getMockBuilder(Relay::class)->setMethods(['process'])->getMock();

        $relayMock->expects($this->atLeast(2))->method('process')->withConsecutive(
            [$data[1], [], true],
            [$data[0], [], true]
        );

        $this->subject->injectRelay($relayMock);

        $this->buildTestCaseForTsfe(24);
        $this->assertEquals(
            'INFO: 2 log entries re-sent.',
            $this->subject->run(__DIR__ . '/../../Fixtures/valid_log.xml', 24)
        );
        $this->assertEquals(24, $GLOBALS['TSFE']->id);
    }
}
