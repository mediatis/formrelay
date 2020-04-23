<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\ValueMapFieldMapper;
use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\GeneralValueMapper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ValueMapFieldMapperTest extends UnitTestCase
{
    protected $subject;

    /**
     * @test
     */
    public function testValueMapperCreation()
    {
        $config = ['foo' => 'bar'];
        $context = [];
        $result = [];

        $generalValueMapperMock = $this->getMockBuilder(GeneralValueMapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();
        $generalValueMapperMock->expects($this->atLeast(1))
            ->method('resolve')
            ->with($context)
            ->willReturn('bar');

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $objectManagerMock->expects($this->atLeast(1))
            ->method('get')
            ->with(GeneralValueMapper::class, $config)
            ->willReturn($generalValueMapperMock);

        $this->subject = new ValueMapFieldMapper($config);
        $this->subject->injectObjectManager($objectManagerMock);

        $this->subject->prepare($context, $result);
        $returnValue = $this->subject->finish($context, $result);

        $this->assertEquals('bar', $context['value']);
        $this->assertEquals(false, $returnValue);
    }
}
