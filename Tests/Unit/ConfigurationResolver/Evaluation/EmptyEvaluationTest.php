<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\EmptyEvaluation;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
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

    /**
     * @test
     */
    public function emptyEvaluationPassesMultiValue()
    {
        $this->subject = new EmptyEvaluation('1');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField([])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function emptyEvaluationDoesNotPassMultiValue()
    {
        $this->subject = new EmptyEvaluation('1');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_1'])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function notEmptyEvaluationPassesMultiValue()
    {
        $this->subject = new EmptyEvaluation('0');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_1'])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function notEmptyEvaluationDoesNotPassMultiValue()
    {
        $this->subject = new EmptyEvaluation('0');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField([])],
            'key' => 'key_1',
        ]);
        $this->assertEquals(false, $result);
    }
}
