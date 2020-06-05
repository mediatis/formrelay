<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\DiscreteFieldFieldMapper;
use Mediatis\Formrelay\Domain\Model\FormField\DiscreteMultiValueFormField;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class DiscreteFieldFieldMapperTest extends AbstractFieldMapperTest
{
    protected function getSubjectClass()
    {
        return DiscreteFieldFieldMapper::class;
    }

    protected function performTest(&$context, &$result, $expectedResult = null, $expectedReturnValue = true)
    {
        return parent::performTest($context, $result, $expectedResult, $expectedReturnValue);
    }

    public function testNewAmongstOthersField()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => 'value_1',
        ];
        $result = ['key_0' => 'value_0', 'key_2' => 'value_2'];
        $expectedResult = ['key_0' => 'value_0', 'key_2' => 'value_2', 'key_1' => new DiscreteMultiValueFormField(['value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testScalarValueFieldNotSet()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => 'value_1',
        ];
        $result = [];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testMultiValueFieldNotSet()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => new MultiValueFormField(['value_1']),
        ];
        $result = [];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testScalarValueFieldSetScalarValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => 'value_1',
        ];
        $result = ['key_1' => 'value_0'];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testMultiValueFieldSetScalarValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => new MultiValueFormField(['value_1']),
        ];
        $result = ['key_1' => 'value_0'];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testScalarValueFieldSetMultiValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => 'value_1',
        ];
        $result = ['key_1' => new MultiValueFormField(['value_0'])];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testMultiValueFieldSetMultiValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => new MultiValueFormField(['value_1']),
        ];
        $result = ['key_1' => new MultiValueFormField(['value_0'])];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testScalarValueFieldSetDiscreteMultiValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => 'value_1',
        ];
        $result = ['key_1' => new DiscreteMultiValueFormField(['value_0'])];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }

    public function testMultiValueFieldSetDiscreteMultiValue()
    {
        $context = [
            'mappedKey' => 'key_1',
            'value' => new MultiValueFormField(['value_1']),
        ];
        $result = ['key_1' => new DiscreteMultiValueFormField(['value_0'])];
        $expectedResult = ['key_1' => new DiscreteMultiValueFormField(['value_0', 'value_1'])];
        $this->createTest();
        $this->performTest($context, $result, $expectedResult);
    }
}
