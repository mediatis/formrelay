<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\NegateFieldMapper;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class NegateFieldMapperTest extends AbstractFieldMapperTest
{
    protected function getSubjectClass()
    {
        return NegateFieldMapper::class;
    }

    protected function performTest(&$context, &$result, $expectedResult = null, $expectedReturnValue = false)
    {
        return parent::performTest($context, $result, $expectedResult, $expectedReturnValue);
    }

    public function testScalarValueNotEmpty()
    {
        $context = [ 'value' => 'something-that-is-not-empty', ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals('0', $context['value']);
    }

    public function testScalarValueOne()
    {
        $context = [ 'value' => '1', ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals('0', $context['value']);
    }

    public function testScalarValueZero()
    {
        $context = [ 'value' => '0', ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals('1', $context['value']);
    }

    public function testScalarValueEmpty()
    {
        $context = [ 'value' => '', ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals('1', $context['value']);
    }

    public function testScalarValueOneWithConfig()
    {
        $context = [ 'value' => '1', ];
        $result = [];
        $this->createTest(['false' => 'someValueRepresentingFalse']);
        $this->performTest($context, $result);
        $this->assertEquals('someValueRepresentingFalse', $context['value']);
    }

    public function testScalarValueZeroWithConfig()
    {
        $context = [ 'value' => '0', ];
        $result = [];
        $this->createTest(['true' => 'someValueRepresentingTrue']);
        $this->performTest($context, $result);
        $this->assertEquals('someValueRepresentingTrue', $context['value']);
    }

    public function testMultiValueTrue()
    {
        $context = [ 'value' => new MultiValueFormField(['1']) ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals(MultiValueFormField::class, get_class($context['value']));
        $this->assertEquals(['0'], iterator_to_array($context['value']));
    }

    public function testMultiValueFalse()
    {
        $context = [ 'value' => new MultiValueFormField(['0']) ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals(MultiValueFormField::class, get_class($context['value']));
        $this->assertEquals(['1'], iterator_to_array($context['value']));
    }

    public function testMultiValueMixed()
    {
        $context = [ 'value' => new MultiValueFormField(['0','something','1','','foobar']) ];
        $result = [];
        $this->createTest();
        $this->performTest($context, $result);
        $this->assertEquals(MultiValueFormField::class, get_class($context['value']));
        $this->assertEquals(['1','0','0','1','0'], iterator_to_array($context['value']));
    }

    public function testMultiValueMixedWithConfig()
    {
        $context = [ 'value' => new MultiValueFormField(['0','something','1','','foobar']) ];
        $result = [];
        $this->createTest(['true' => 'someValueRepresentingTrue', 'false' => 'someValueRepresentingFalse']);
        $this->performTest($context, $result);
        $this->assertEquals(MultiValueFormField::class, get_class($context['value']));
        $this->assertEquals(
            [
                'someValueRepresentingTrue',
                'someValueRepresentingFalse',
                'someValueRepresentingFalse',
                'someValueRepresentingTrue',
                'someValueRepresentingFalse'
            ],
            iterator_to_array($context['value'])
        );
    }
}
