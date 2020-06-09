<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Test\Unit\Service;

use Mediatis\Formrelay\Configuration\CliConfigurationManager;
use Mediatis\Formrelay\Exceptions\InvalidXmlException;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use Mediatis\Formrelay\Service\Relay;
use Mediatis\Formrelay\Service\FormSimulatorService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class FormSimulatorServiceTest extends UnitTestCase
{
    /** @var FormSimulatorService */
    private $subject;

    protected $relayMock;
    protected $cliConfigurationManagerMock;

    protected $fixturePath = __DIR__ . '/../../Fixtures/';

    public function setUp(): void
    {
        parent::setUp();
        $subject = new FormSimulatorService();
        $this->subject = $subject;

        $this->relayMock = $this->getMockBuilder(Relay::class)
            ->setMethods(['process'])
            ->getMock();
        $this->subject->injectRelay($this->relayMock);

        $this->cliConfigurationManagerMock = $this->getMOckBuilder(CliConfigurationManager::class)
            ->setMethods(['getTypoScriptSetup', 'setTypoScriptSetup'])
            ->getMock();
        $this->subject->injectCliConfigurationManager($this->cliConfigurationManagerMock);

        // this class is providing static code only, so we will not mock it
        $this->subject->injectTypoScriptParser(new TypoScriptParser());

    }

    public function tearDown(): void
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
        $this->relayMock->expects($this->never())->method('process');
        $this->expectException(InvalidXmlFileException::class);
        $this->subject->run('This_file_does_not_exist', $this->fixturePath . 'setup.typoscript', 0);
    }

    /**
     * @test
     */
    public function runWithInvalidXmlDataThrowsError()
    {
        $this->relayMock->expects($this->never())->method('process');
        $this->expectException(InvalidXmlException::class);
        $this->subject->run($this->fixturePath . 'invalid_log.xml', $this->fixturePath . 'setup.typoscript', 0);
    }

    /**
     * @test
     */
    public function runReturnsResultMessageAfterNothingIsParsed()
    {
        $this->relayMock->expects($this->never())->method('process');
        $this->assertEquals(
            'INFO: 0 log entries re-sent.',
            $this->subject->run($this->fixturePath . 'valid_log_nothing_to_send.xml', $this->fixturePath . 'setup.typoscript', 0)
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


        $this->relayMock->expects($this->atLeast(2))->method('process')->withConsecutive(
            [$data[1], [], true],
            [$data[0], [], true]
        );
        $this->assertEquals(
            'INFO: 2 log entries re-sent.',
            $this->subject->run($this->fixturePath . 'valid_log.xml', $this->fixturePath . 'setup.typoscript', 0)
        );
    }
}
