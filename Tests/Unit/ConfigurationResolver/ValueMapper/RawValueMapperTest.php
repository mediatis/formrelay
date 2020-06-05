<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\RawValueMapper;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class RawValueMapperTest extends AbstractValueMapperTest
{
    protected function getSubjectClass()
    {
        return RawValueMapper::class;
    }

    public function testNonExistingValue()
    {
        $config = ['value_2' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1', $result);
    }

    public function testExistingValue()
    {
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    public function testMultipleRawValues()
    {
        $config = ['value_0' => [], 'value_1' => [], 'value_2' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    public function testNonExistingMultiValue()
    {
        $multiValue = new MultiValueFormField(['value_1']);
        $config = ['value_2' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1'], iterator_to_array($result));
    }

    public function testExistingMultiValue()
    {
        $multiValue = new MultiValueFormField(['value_1']);
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1b'], iterator_to_array($result));
    }

    public function testMixedMultiValue()
    {
        $multiValue = new MultiValueFormField(['value_1', 'value_3', 'value_4']);
        $config = ['value_1' => [], 'value_2' => [], 'value_4' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context),
            $this->buildValueMappernMock('value_4b', $context),
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1b', 'value_3', 'value_4b'], iterator_to_array($result));
    }
}
