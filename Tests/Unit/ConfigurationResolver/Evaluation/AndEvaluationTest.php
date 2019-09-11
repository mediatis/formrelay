<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\AndEvaluation;
use Mediatis\Formrelay\ConfigurationResolver\Evaluation\Evaluation;
use Mediatis\Formrelay\ConfigurationResolver\Evaluation\EqualsEvaluation;
use Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class AndEvaluationTest extends UnitTestCase
{
    /** @var MockObject */
    protected $objectManagerMock;

    /** @var MockObject */
    protected $signalSlotDispatcherMock;

    protected $subject;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->signalSlotDispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $this->subject = new AndEvaluation([]);
        $this->subject->injectObjectManager($this->objectManagerMock);
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcherMock);
    }

    protected function buildEvaluationMock($evalReturnValue, $expectedArgs = null, $expectInvoke = true)
    {
        $evaluationMock = $this->getMockBuilder(Evaluation::class)->disableOriginalConstructor()->setMethods(['eval'])->getMock();
        $invocationMock = $evaluationMock->expects($expectInvoke ? $this->exactly(1) : $this->never())->method('eval');
        if (is_array($expectedArgs)) {
            $invocationMock->with($expectedArgs);
        }
        $invocationMock->willReturn($evalReturnValue);
        return $evaluationMock;
    }

    public function provideBothSides()
    {
        return [[true], [false]];
    }

    /**
     * @test
     */
    public function useSubEvaluation()
    {
        $subEvalClassName = 'Some\\Other\\Evaluation';
        $context = ['key' => 'key_x'];
        $config = [$subEvalClassName => ['key_1' => 'value_1']];
        ObjectAccess::setProperty($this->subject, 'config', $config, true);
        $this->objectManagerMock->expects($this->atLeast(1))->method('get')
            ->with($subEvalClassName, $config[$subEvalClassName])
            ->willReturn($this->buildEvaluationMock(true, $context));
        $result = $this->subject->eval($context);
        $this->assertEquals(true, $result);
    }

    // @TODO test useSubEvalutionByKeywordk()

    /**
     * @test
     */
    public function subConfigurationWithContextChange()
    {
        $fieldName = 'field_1';
        $context = ['key' => 'key_x'];
        $expectedContext = ['key' => $fieldName];
        $config = [$fieldName => ['key_1' => 'value_1']];
        ObjectAccess::setProperty($this->subject, 'config', $config, true);
        $this->objectManagerMock->expects($this->atLeast(1))->method('get')
            ->withConsecutive(
                [$fieldName, $config[$fieldName]],
                ['general', $config[$fieldName]]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->buildEvaluationMock(true, $expectedContext)
            );
        $result = $this->subject->eval($context);
        $this->assertEquals(true, $result);
    }

    // @TODO implement test for the is_numeric part of the algorithm

    /**
     * @test
     * @dataProvider provideBothSides
     * @param bool $expectedResult
     */
    public function defaultsToEqualsEvaluation($expectedResult)
    {
        $configKey = 'key_1';
        $configValue = 'value_1';
        $config = [$configKey => $configValue];
        ObjectAccess::setProperty($this->subject, 'config', $config, true);
        $this->objectManagerMock->expects($this->atLeast(1))->method('get')
            ->withConsecutive(
                [$configKey, $configValue],
                ['equals', $configValue]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->buildEvaluationMock($expectedResult, ['key' => $configKey])
            );

        $result = $this->subject->eval();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider provideBothSides
     * @param bool $expectedResult
     */
    public function testListOneAlwaysPasses($expectedResult)
    {
        $configKeys = ['key_1', 'key_2'];
        $configValues = ['value_1', 'value_2'];
        $config = [
            $configKeys[0] => $configValues[0],
            $configKeys[1] => $configValues[1],
        ];
        ObjectAccess::setProperty($this->subject, 'config', $config, true);
        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [$configKeys[1], $configValues[1], null],
                [$configKeys[0], $configValues[0], null],
                ['equals', $configValues[0], $this->buildEvaluationMock(true, ['key' => $configKeys[0]])],
                ['equals', $configValues[1], $this->buildEvaluationMock($expectedResult, ['key' => $configKeys[1]])],
            ]);
        $result = $this->subject->eval();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider provideBothSides
     * @param bool $expectedResult
     */
    public function testListOneNeverPasses($expectedResult)
    {
        $configKeys = ['key_1', 'key_2'];
        $configValues = ['value_1', 'value_2'];
        $config = [
            $configKeys[0] => $configValues[0],
            $configKeys[1] => $configValues[1],
        ];
        ObjectAccess::setProperty($this->subject, 'config', $config, true);

        $this->objectManagerMock->expects($this->atLeast(2))->method('get')
            ->withConsecutive(
                [$configKeys[0], $configValues[0]],
                ['equals', $configValues[0]],
                [$configKeys[1], $configValues[1]],
                ['equals', $configValues[1]]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->buildEvaluationMock(false, ['key' => $configKeys[0]]),
                null,
                $this->buildEvaluationMock($expectedResult, ['key' => $configKeys[1]], false)
            );

        $result = $this->subject->eval();
        $this->assertEquals(false, $result);
    }
}
