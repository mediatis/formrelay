<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ValueMapper;

use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\SwitchValueMapper;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class SwitchValueMapperTest extends AbstractValueMapperTest
{
    protected function getSubjectClass()
    {
        return SwitchValueMapper::class;
    }

    public function provideCaseKeywords()
    {
        return [['_typoScriptNodeValue', 'case'], ['case', '_typoScriptNodeValue']];
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     */
    public function testNonExistingValue($caseKeyword)
    {
        $config = [1 => [$caseKeyword => 'value_2']];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1', $result);
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     */
    public function testExistingValue($caseKeyword)
    {
        $config = [1 => [$caseKeyword => 'value_1']];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     * @param $otherCaseKeyword
     */
    public function testMultipleRawValues($caseKeyword, $otherCaseKeyword)
    {
        $config = [1 => [$caseKeyword => 'value_0'], 2 => [$otherCaseKeyword => 'value_1'], 3 => [$caseKeyword => 'value_2']];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => 'value_1'] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals('value_1b', $result);
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     */
    public function testNonExistingMultiValue($caseKeyword)
    {
        $multiValue = new MultiValueFormField(['value_1']);
        $config = [1 => [$caseKeyword => 'value_2']];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1'], iterator_to_array($result));
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     */
    public function testExistingMultiValue($caseKeyword)
    {
        $multiValue = new MultiValueFormField(['value_1']);
        $config = [1 => [$caseKeyword => 'value_1']];
        $context = [ 'key' => 'key_1', 'data' => ['key_1' => $multiValue] ];

        $this->prepareObjectManager([
            $this->buildValueMappernMock('value_1b', $context)
        ]);

        $result = $this->performTest($config, $context);
        $this->assertEquals(MultiValueFormField::class, get_class($result));
        $this->assertEquals(['value_1b'], iterator_to_array($result));
    }

    /**
     * @test
     * @dataProvider provideCaseKeywords
     * @param $caseKeyword
     * @param $otherCaseKeyword
     */
    public function testMixedMultiValue($caseKeyword, $otherCaseKeyword)
    {
        $multiValue = new MultiValueFormField(['value_1', 'value_3', 'value_4']);
        $config = [
            1 => [$caseKeyword => 'value_1'],
            2 => [$caseKeyword => 'value_2'],
            3 => [$otherCaseKeyword => 'value_4'],
        ];
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
