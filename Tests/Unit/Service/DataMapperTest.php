<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Service\DataMapper;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class DataMapperTest extends UnitTestCase
{
    /**
     * @var DataMapper
     */
    private $subject;

    protected static function getMethod($name) {
        $class = new \ReflectionClass(DataMapper::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockForAbstractClass(DataMapper::class);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testMethodProcessFieldDefaultBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped_value', 'mapped_key', []]);
        $this->assertEquals(['mapped_key' => 'mapped_value'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldIgnoreBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped_value', 'mapped_key', ['ignore' => '1']]);
        $this->assertEquals([], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldPassthroughBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped_value', 'mapped_key', ['passthrough' => '1']]);
        $this->assertEquals(['original_key' => 'mapped_value'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldSplitBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped val ue', 'mapped_key', ['split.' => ['fields.' => ['1' => 'mapped_key_1', '2' => 'mapped_key_2']]]]);
        $this->assertEquals(['mapped_key_1' => 'mapped', 'mapped_key_2' => 'val ue'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldDistributeBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped_value', 'mapped_key', ['distribute.' => ['fields.' => ['1' => 'mapped_key_1', '2' => 'mapped_key_2']]]]);
        $this->assertEquals(['mapped_key_1' => 'mapped_value', 'mapped_key_2' => 'mapped_value'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldJoinBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $mappedValue = new FormFieldMultiValue(['key_1', 'key_2']);
        $method->invokeArgs($this->subject, [&$result, 'original_key', $mappedValue, 'mapped_key', ['join.' => ['glue' => ',']]]);
        $this->assertEquals(['mapped_key' => 'key_1,key_2'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldNegateBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key', 'mapped_value', 'mapped_key', ['negate' => '1']]);
        $this->assertEquals(['mapped_key' => '0'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldAppendValueBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key_1', 'mapped_value_1', 'mapped_key', ['appendValue.' => ['separator' => ';']]]);
        $method->invokeArgs($this->subject, [&$result, 'original_key_2', 'mapped_value_2', 'mapped_key', ['appendValue.' => ['separator' => ';']]]);
        $this->assertEquals(['mapped_key' => 'mapped_value_1;mapped_value_2'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldAppendKeyValueBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key_1', 'mapped_value_1', 'mapped_key', ['appendKeyValue.' => ['separator' => ';']]]);
        $method->invokeArgs($this->subject, [&$result, 'original_key_2', 'mapped_value_2', 'mapped_key', ['appendKeyValue.' => ['separator' => ';']]]);
        $this->assertEquals(['mapped_key' => 'original_key_1 = mapped_value_1;original_key_2 = mapped_value_2;'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldIfEmptyBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key_1', 'mapped_value_1', 'mapped_key', []]);
        $method->invokeArgs($this->subject, [&$result, 'original_key_2', 'mapped_value_2', 'mapped_key', ['ifEmpty' => '1']]);
        $this->assertEquals(['mapped_key' => 'mapped_value_1'], $result);
    }

    /**
     * @test
     */
    public function testMethodProcessFieldDistributeAndAppendValueBehaviour()
    {
        $method = self::getMethod('processField');
        $result = [];
        $method->invokeArgs($this->subject, [&$result, 'original_key_1', 'mapped_value_1', 'mapped_key_2', []]);
        $method->invokeArgs($this->subject, [&$result, 'original_key_2', 'mapped_value_2', '', [
            'distribute.' => [
                'fields.' => [
                    '1' => 'mapped_key_1',
                    '2' => 'mapped_key_2',
                    '2.' => ['appendValue.' => ['separator' => ';']],
                ]
            ]
        ]]);
        $this->assertEquals(['mapped_key_1' => 'mapped_value_2', 'mapped_key_2' => 'mapped_value_1;mapped_value_2'], $result);
    }

    // @TODO add unit tests for DataMapper::processAllFields()

}
