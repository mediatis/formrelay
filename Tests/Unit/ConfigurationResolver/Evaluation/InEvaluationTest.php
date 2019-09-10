<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\InEvaluation;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class InEvaluationTest extends UnitTestCase
{
    protected $subject;

    /**
     * @test
     */
    public function evaluationPasses()
    {
        $this->subject = new InEvaluation('value_1,value_2');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_2'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationDoesNotPass()
    {
        $this->subject = new InEvaluation('value_1,value_3');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_2'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayPasses()
    {
        $this->subject = new InEvaluation(['value_1','value_2']);
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_2'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayDoesNotPass()
    {
        $this->subject = new InEvaluation(['value_1','value_3']);
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_2'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }
}
