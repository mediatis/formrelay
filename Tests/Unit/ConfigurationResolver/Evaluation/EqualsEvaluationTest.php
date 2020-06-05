<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\EqualsEvaluation;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class EqualsEvaluationTest extends UnitTestCase
{
    protected $subject;

    /**
     * @test
     */
    public function testEvaluationPasses()
    {
        $this->subject = new EqualsEvaluation('value_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function testEvaluationDoesNotPass()
    {
        $this->subject = new EqualsEvaluation('value_1_b');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function testEvaluationPassesMultiValue()
    {
        $this->subject = new EqualsEvaluation('value_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_0', 'value_1', 'value_2'])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function testEvaluationDoesNotPassMultiValue()
    {
        $this->subject = new EqualsEvaluation('value_1_b');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_0', 'value_1', 'value_2'])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }
}
