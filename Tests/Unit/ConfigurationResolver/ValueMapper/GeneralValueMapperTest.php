<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\GeneralValueMapper;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class GeneralValueMapperTest extends AbstractValueMapperTest
{
    protected function getSubjectClass()
    {
        return GeneralValueMapper::class;
    }

    public function testKeywordValueMapper()
    {
        $config = ['someKeyword' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    public function testMatchedKeyValueMapper()
    {
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            null,
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    public function testNonExistentValueMapper()
    {
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            null,
            null,
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1', $result);
    }

    public function testNullReturningValueMapper()
    {
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock(null, $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1', $result);
    }

    public function testMultiValue()
    {
        $multiValue = new MultiValueFormField(['value_1', 'value_2']);
        $config = ['value_1' => []];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context),
            $this->buildValueMappernMock('value_2b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1b', 'value_2b'], iterator_to_array($result));
    }
}
