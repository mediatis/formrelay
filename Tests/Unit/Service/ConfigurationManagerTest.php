<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class ConfigurationManagerTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    private $subject;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ConfigurationManager();

        $this->signalSlotDispatcher = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->signalSlotDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->willReturnArgument(2);
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcher);

        $this->subject->setSetupOverwrite([]);
    }

    /**
     * @test
     */
    public function loadBaseSettings()
    {
        $config = [
            'ext_key_1' => ['settings' => ['some_key' => 'some_value']]
        ];

        $this->subject->setSetup($config);
        $result = $this->subject->getFormrelayCycles('ext_key_1');
        $this->assertEquals([0 => ['some_key' => 'some_value']], $result);
    }

    /**
     * @test
     */
    public function loadSubSettings()
    {
        $config = [
            'ext_key_1' => ['settings' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0' => ['key_1' => 'value_1_b'],
                '1' => ['key_1' => 'value_1_c']
            ]]
        ];
        $this->subject->setSetup($config);
        $result = $this->subject->getFormrelayCycles('ext_key_1');
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
            'ext_key_1' => ['settings' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
            ]]
        ];

        $overwriteConfig = [
            'ext_key_1' => ['settings' => ['key_2' => 'value_2_b']]
        ];

        $this->subject->setSetup($config);
        $this->subject->setSetupOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelayCycles('ext_key_1');
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
            'ext_key_1' => ['settings' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0' => ['key_1' => 'value_1_b'],
                '1' => ['key_1' => 'value_1_c']
            ]]
        ];

        $overwriteConfig = [
            'ext_key_1' => ['settings' => [
                'key_2' => 'value_2_b',
                '0' => ['key_1' => 'value_1_d'],
                '1' => [],
            ]]
        ];

        $this->subject->setSetup($config);
        $this->subject->setSetupOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelayCycles('ext_key_1');
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
            'ext_key_1' => ['settings' => ['some_array_key' => ['some_key' => 'some_value']]]
        ];

        $overwriteConfig = [];

        $this->subject->setSetup($config);
        $this->subject->setSetupOverwrite($overwriteConfig);

        $result = $this->subject->getFormrelayCycles('ext_key_1');
        $this->assertEquals([0 => ['some_array_key' => ['some_key' => 'some_value']]], $result);
    }
}
