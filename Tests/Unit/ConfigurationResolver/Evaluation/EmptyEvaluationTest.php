<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\EmptyEvaluation;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class EmptyEvaluationTest extends UnitTestCase
{
    protected $subject;

    /**
     * @test
     */
    public function emptyEvaluationPasses()
    {
        $this->subject = new EmptyEvaluation('1');
        $result = $this->subject->eval([
            'data' => ['key_1' => ''],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function emptyEvaluationDoesNotPass()
    {
        $this->subject = new EmptyEvaluation('1');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function notEmptyEvaluationPasses()
    {
        $this->subject = new EmptyEvaluation('0');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function notEmptyEvaluationDoesNotPass()
    {
        $this->subject = new EmptyEvaluation('0');
        $result = $this->subject->eval([
            'data' => ['key_1' => ''],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }
}
