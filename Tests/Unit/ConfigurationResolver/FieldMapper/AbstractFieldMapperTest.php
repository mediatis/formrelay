<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\FieldMapper;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Nimut\TestingFramework\TestCase\UnitTestCase;

abstract class AbstractFieldMapperTest extends UnitTestCase
{
    protected $subject;

    abstract protected function getSubjectClass();

    protected function createTest($config = [])
    {
        $class = $this->getSubjectClass();
        $this->subject = new $class($config);
    }

    protected function checkResult($result, $expectedResult)
    {
        foreach ($expectedResult as $key => $value) {
            if (!isset($result[$key])) {
                $this->fail('Result does not contain key "' . $key . '"');
            }
            if ($value instanceof MultiValueFormField) {
                $this->assertEquals(get_class($value), get_class($result[$key]));
                $this->assertEquals((string)$value, (string)$result[$key]);
            } else {
                $this->assertEquals($value, $result[$key]);
            }
        }
        foreach ($result as $key => $value) {
            if (!isset($expectedResult[$key])) {
                $this->fail('Result contains unexpected key "' . $key . '".');
            }
        }
    }

    protected function performTest(&$context, &$result, $expectedResult = null, $expectedReturnValue = null)
    {
        $this->subject->prepare($context, $result);
        $returnValue = $this->subject->finish($context, $result);

        if ($expectedReturnValue !== null) {
            $this->assertEquals($expectedReturnValue, $returnValue);
        }

        if ($expectedResult !== null) {
            $this->checkResult($result, $expectedResult);
        }

        return $returnValue;
    }
}
