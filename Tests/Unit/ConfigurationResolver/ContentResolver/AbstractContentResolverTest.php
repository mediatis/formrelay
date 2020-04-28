<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ContentResolver;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

abstract class AbstractContentResolverTest extends UnitTestCase
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

    protected function createTest($config)
    {
        $class = $this->getSubjectClass();
        $this->subject = new $class($config);
        $this->subject->injectObjectManager($this->objectManagerMock);
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcherMock);
    }

    protected function performTest($context, $expectedResult, $result = null)
    {
        if ($result === null) {
            $result = $this->subject->build($context);
        }
        $this->subject->finish($context, $result);
        $this->assertEquals($expectedResult, $result);
        return $result;
    }
}
