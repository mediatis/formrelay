<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\ValueMapper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Object\Exception as ObjectException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

abstract class AbstractValueMapperTest extends UnitTestCase
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
    }

    abstract protected function getSubjectClass();

    protected function buildValueMappernMock($resolveReturnValue, $expectedArgs = null, $expectInvoke = true)
    {
        $valueMapperMock = $this->getMockBuilder(ValueMapper::class)->disableOriginalConstructor()->setMethods(['resolve'])->getMock();
        $invocationMock = $valueMapperMock->expects($expectInvoke ? $this->any() : $this->never())->method('resolve');
        if (is_array($expectedArgs)) {
            $invocationMock->with($expectedArgs);
        }
        $invocationMock->willReturn($resolveReturnValue);
        return $valueMapperMock;
    }

    protected function prepareObjectManager($subMappers)
    {
        foreach ($subMappers as $index => $subMapper) {
            if ($subMapper === null) {
                $subMappers[$index] = $this->throwException(new ObjectException());
            }
        }
        $this->objectManagerMock
            ->expects($this->atLeast(1))
            ->method('get')
            ->willReturnOnConsecutiveCalls(...$subMappers);
    }

    protected function performTest($config = [], $context = [])
    {
        $class = $this->getSubjectClass();
        $this->subject = new $class($config);
        $this->subject->injectObjectManager($this->objectManagerMock);
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcherMock);
        return $this->subject->resolve($context);
    }
}
