<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\AbstractFormrelayHook;
use Mediatis\Formrelay\Command\FormSimulationCommand;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Service\DataMapper;
use Mediatis\Formrelay\Service\Gate;
use Mediatis\Formrelay\Simulation\FormSimulatorService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class GateTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockForAbstractClass(Gate::class);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function noFiltersInvalid()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['validWithNoFilters' => '0']]]
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function noFiltersValid()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['validWithNoFilters' => '1']]]
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function whitelistMatches()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['filters.' => ['1.' => ['whitelist.' => ['key' => 'value,otherValue']]]]]]
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function whitelistDoesNotMatch()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['filters.' => ['1.' => ['whitelist.' => ['key' => 'otherValue,yetAnotherValue']]]]]]
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function blacklistMatches()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['filters.' => ['1.' => ['blacklist.' => ['key' => 'value,otherValue']]]]]]
        );
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function blacklistDoesNotMatch()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => ['filters.' => ['1.' => ['blacklist.' => ['key' => 'otherValue,yetAnotherValue']]]]]]
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function twoFiltersSecondValid()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => [
                'validWithNoFilters' => '1',
                'filters.' => [
                    '1.' => ['blacklist.' => ['key' => 'value,otherValue']],
                    '2.' => ['whitelist.' => ['key' => 'value']],
                ]
            ]]]
        );
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     */
    public function twoFiltersNoneValid()
    {
        $result = $this->subject->validateForm(
            ['key' => 'value'],
            ['fields.' => ['validation.' => [
                'validWithNoFilters' => '1',
                'filters.' => [
                    '1.' => ['blacklist.' => ['key' => 'value,otherValue']],
                    '2.' => ['blacklist.' => ['key' => 'value']],
                ]
            ]]]
        );
        $this->assertEquals(false, $result);
    }

    //@TODO add tests for filters equals and equalsNot, need to mock configuration management for that
}
