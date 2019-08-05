<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\AbstractFormrelayHook;
use Mediatis\Formrelay\Command\FormSimulationCommand;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Service\ConfigurationManager;
use Mediatis\Formrelay\Service\DataMapper;
use Mediatis\Formrelay\Service\FormrelayGate;
use Mediatis\Formrelay\Simulation\FormSimulatorService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class FormrelayGateTest extends UnitTestCase
{
    /**
     * @var FormrelayGate
     */
    private $subject;

    /**
     * @var MockObject
     */
    private $configurationManagerMock;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new FormrelayGate();
        $this->configurationManagerMock = $this->getMockBuilder(ConfigurationManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSettings', 'getSettingsCount'])
            ->getMock();
        $this->subject->injectConfigurationmanager($this->configurationManagerMock);
    }

    protected function setConfiguration($conf) {
        $settingsValueMap = [];
        $settingsCountValueMap = [];
        foreach ($conf as $extKey => $extConf) {
            $settingsCountValueMap[] = [$extKey, count($extConf)];
            foreach ($extConf as $index => $extInstanceConf) {
                $settingsValueMap[] = [$extKey, $index, $extInstanceConf];
            }
        }
        $this->configurationManagerMock
            ->expects($this->any())
            ->method('getSettings')
            ->will($this->returnValueMap($settingsValueMap));

        $this->configurationManagerMock
            ->expects($this->any())
            ->method('getSettingsCount')
            ->will($this->returnValueMap($settingsCountValueMap));

    }

    /**
     * @test
     */
    public function noFiltersInvalid()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function noFiltersValid()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '1']]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function whitelistMatches()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['whitelist.' => ['key' => 'value,otherValue']]]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function whitelistDoesNotMatch()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['whitelist.' => ['key' => 'otherValue,yetAnotherValue']]]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function blacklistMatches()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['blacklist.' => ['key' => 'value,otherValue']]]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function blacklistDoesNotMatch()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['blacklist.' => ['key' => 'otherValue,yetAnotherValue']]]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function twoFiltersSecondValid()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => [
                '1.' => ['blacklist.' => ['key' => 'value,otherValue']],
                '2.' => ['whitelist.' => ['key' => 'value']]
            ]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function twoFiltersNoneValid()
    {
        $this->setConfiguration(['ext_key_1' => [
            0 => ['fields.' => ['gate.' => ['filters.' => [
                '1.' => ['blacklist.' => ['key' => 'value,otherValue']],
                '2.' => ['blacklist.' => ['key' => 'value']]
            ]]]]
        ]]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function equalsExternalValidationValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equals' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '1']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function equalsExternalValidationNotValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equals' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function equalsNotExternalValidationValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equalsNot' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function equalsNotExternalValidationNotValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equalsNot' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '1']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function equalsExternalValidationWithTwoInstancesValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equals' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]],
                1 => ['fields.' => ['gate.' => ['validWithNoFilters' => '1']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function equalsExternalValidationWithTwoInstancesNotValid()
    {
        $this->setConfiguration([
            'ext_key_1' => [
                0 => ['fields.' => ['gate.' => ['filters.' => ['1.' => ['equals' => 'ext_key_2']]]]]
            ],
            'ext_key_2' => [
                0 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]],
                1 => ['fields.' => ['gate.' => ['validWithNoFilters' => '0']]]
            ]
        ]);
        $result = $this->subject->permit(
            ['key' => 'value'],
            'ext_key_1',
            0
        );
        $this->assertEquals(false, $result);
    }
}
