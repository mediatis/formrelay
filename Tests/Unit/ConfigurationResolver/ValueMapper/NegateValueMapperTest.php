<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\NegateValueMapper;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class NegateValueMapperTest extends AbstractValueMapperTest
{
    protected function getSubjectClass()
    {
        return NegateValueMapper::class;
    }

    public function testScalarValueNotEmpty()
    {
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'something-that-is-not-empty'] ];

        $valueMapperMock = $this->buildValueMappernMock('something-that-is-not-empty', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('0', $result);
    }

    public function testScalarValueOne()
    {
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => '1'] ];

        $valueMapperMock = $this->buildValueMappernMock('1', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('0', $result);
    }

    public function testScalarValueEmpty()
    {
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => ''] ];

        $valueMapperMock = $this->buildValueMappernMock('', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('1', $result);
    }

    public function testScalarValueZero()
    {
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => '0'] ];

        $valueMapperMock = $this->buildValueMappernMock('0', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('1', $result);
    }

    public function testScalarValueOneWithConfig()
    {
        $config = ['false' => 'someValueRepresentingFalse'];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => '1'] ];

        $valueMapperMock = $this->buildValueMappernMock('1', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('someValueRepresentingFalse', $result);
    }

    public function testScalarValueZeroWithConfig()
    {
        $config = ['true' => 'someValueRepresentingTrue'];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => '0'] ];

        $valueMapperMock = $this->buildValueMappernMock('0', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('someValueRepresentingTrue', $result);
    }

    public function testMultiValueTrue()
    {
        $multiValue = new MultiValueFormField(['1']);
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $valueMapperMock = $this->buildValueMappernMock($multiValue, $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['0'], iterator_to_array($result));
    }

    public function testMultiValueFalse()
    {
        $multiValue = new MultiValueFormField(['0']);
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $valueMapperMock = $this->buildValueMappernMock('0', $context);
        $this->prepareObjectManager([$valueMapperMock]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['1'], iterator_to_array($result));
    }

    public function testMultiValueMixed()
    {
        $multiValue = new MultiValueFormField(['0', 'something', '1', '', 'foobar']);
        $config = [];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('0', $context),
            $this->buildValueMappernMock('something', $context),
            $this->buildValueMappernMock('1', $context),
            $this->buildValueMappernMock('', $context),
            $this->buildValueMappernMock('foobar', $context),
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['1','0','0','1','0'], iterator_to_array($result));
    }

    public function testMultiValueMixedWithConfig()
    {
        $multiValue = new MultiValueFormField(['0', 'something', '1', '', 'foobar']);
        $config = ['true' => 't', 'false' => 'f'];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('0', $context),
            $this->buildValueMappernMock('something', $context),
            $this->buildValueMappernMock('1', $context),
            $this->buildValueMappernMock('', $context),
            $this->buildValueMappernMock('foobar', $context),
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['t','f','f','t','f'], iterator_to_array($result));
    }
}
