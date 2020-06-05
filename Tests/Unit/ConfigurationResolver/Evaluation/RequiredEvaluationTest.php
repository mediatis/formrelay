<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\ConfigurationResolver\Evaluation\RequiredEvaluation;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class RequiredEvaluationTest extends UnitTestCase
{
    protected $subject;

    /**
     * @test
     */
    public function evaluationPasses()
    {
        $this->subject = new RequiredEvaluation('key_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationWIthMissingFieldDoesNotPass()
    {
        $this->subject = new RequiredEvaluation('key_1');
        $result = $this->subject->eval([
            'data' => ['key_2' => 'value_2'],
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function evaluationWIthExistingFieldDoesNotPass()
    {
        $this->subject = new RequiredEvaluation('key_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => ''],
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayPasses()
    {
        $this->subject = new RequiredEvaluation(['key_1']);
        $result = $this->subject->eval([
            'data' => ['key_1' => 'value_1'],
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayDoesNotPass()
    {
        $this->subject = new RequiredEvaluation(['key_1']);
        $result = $this->subject->eval([
            'data' => ['key_1' => ''],
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function evaluationPassesMultiValue()
    {
        $this->subject = new RequiredEvaluation('key_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_1'])],
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationWIthExistingFieldDoesNotPassMultiValue()
    {
        $this->subject = new RequiredEvaluation('key_1');
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField([])],
        ]);
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayPassesMultiValue()
    {
        $this->subject = new RequiredEvaluation(['key_1']);
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField(['value_1'])],
        ]);
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function evaluationWithArrayDoesNotPassMultiValue()
    {
        $this->subject = new RequiredEvaluation(['key_1']);
        $result = $this->subject->eval([
            'data' => ['key_1' => new MultiValueFormField([])],
        ]);
        $this->assertEquals(false, $result);
    }
}
