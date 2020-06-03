<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class ConfigurationManagerTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    private $subject;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var FrontendConfigurationManager */
    protected $frontendConfigurationManagerMock;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new ConfigurationManager();
        $this->frontendConfigurationManagerMock = $this->getMockBuilder(FrontendConfigurationManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypoScriptSetup'])
            ->getMock();
        $this->subject->injectFrontendConfigurationManager($this->frontendConfigurationManagerMock);

        $this->signalSlotDispatcher = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->signalSlotDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->willReturnArgument(2);
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcher);

        $this->subject->setFormrelaySettingsOverwrite([]);

        // we inject the original because this class has no context.
        // it should actually be a static utility class
        $this->subject->injectTypoScriptService(new TypoScriptService());


//        ObjectAccess::setProperty($this->subject, 'formrelayExtSettingsRaw', [], true);
//        ObjectAccess::setProperty($this->subject, 'overwriteFormrelayExtSettingsRaw', [], true);
//        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [], true);
//        ObjectAccess::setProperty($this->subject, 'overwriteSettingsRaw', [], true);
    }

    /**
     * @test
     */
    public function loadBaseSettings()
    {
        $config = [
            'ext_key_1.' => ['settings.' => ['some_key' => 'some_value']]
        ];

        $this->frontendConfigurationManagerMock
            ->expects($this->any())
            ->method('getTypoScriptSetup')
            ->willReturn(['plugin.' => $config]);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([0 => ['some_key' => 'some_value']], $result);
    }

    /**
     * @test
     */
    public function loadSubSettings()
    {
        $config = [
            'ext_key_1.' => ['settings.' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0.' => ['key_1' => 'value_1_b'],
                '1.' => ['key_1' => 'value_1_c']
            ]]
        ];
        $this->frontendConfigurationManagerMock
            ->expects($this->any())
            ->method('getTypoScriptSetup')
            ->willReturn(['plugin.' => $config]);

        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1_b', 'key_2' => 'value_2'],
            1 => ['key_1' => 'value_1_c', 'key_2' => 'value_2'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadSettingsWithOverwrite()
    {
        $config = [
            'ext_key_1.' => ['settings.' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
            ]]
        ];

        $overwriteConfig = [
            'ext_key_1' => ['settings' => ['key_2' => 'value_2_b']]
        ];

        $this->frontendConfigurationManagerMock
            ->expects($this->any())
            ->method('getTypoScriptSetup')
            ->willReturn(['plugin.' => $config]);

        $this->subject->setFormrelaySettingsOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1', 'key_2' => 'value_2_b'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadSubSettingsWithOverwrite()
    {
        $config = [
            'ext_key_1.' => ['settings.' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0.' => ['key_1' => 'value_1_b'],
                '1.' => ['key_1' => 'value_1_c']
            ]]
        ];

        $overwriteConfig = [
            'ext_key_1' => ['settings' => [
                'key_2' => 'value_2_b',
                '0' => ['key_1' => 'value_1_d'],
                '1' => [],
            ]]
        ];

        $this->frontendConfigurationManagerMock
            ->expects($this->any())
            ->method('getTypoScriptSetup')
            ->willReturn(['plugin.' => $config]);

        $this->subject->setFormrelaySettingsOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1_d', 'key_2' => 'value_2_b'],
            1 => ['key_1' => 'value_1_c', 'key_2' => 'value_2_b'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadNonScalarSettings()
    {
        $config = [
            'ext_key_1.' => ['settings.' => ['some_array_key.' => ['some_key' => 'some_value']]]
        ];

        $overwriteConfig = [];

        $this->frontendConfigurationManagerMock
            ->expects($this->any())
            ->method('getTypoScriptSetup')
            ->willReturn(['plugin.' => $config]);

        $this->subject->setFormrelaySettingsOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([0 => ['some_array_key' => ['some_key' => 'some_value']]], $result);
    }
}
