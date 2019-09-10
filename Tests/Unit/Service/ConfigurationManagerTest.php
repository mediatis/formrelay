<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit\Service;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ConfigurationManagerTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new ConfigurationManager();
        ObjectAccess::setProperty($this->subject, 'formrelayExtSettingsRaw', [], true);
        ObjectAccess::setProperty($this->subject, 'overwriteFormrelayExtSettingsRaw', [], true);
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [], true);
        ObjectAccess::setProperty($this->subject, 'overwriteSettingsRaw', [], true);
    }

    /**
     * @test
     */
    public function loadBaseSettings()
    {
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [
            'ext_key_1' => ['some_key' => 'some_value']
        ], true);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([0 => ['some_key' => 'some_value']], $result);
    }

    /**
     * @test
     */
    public function loadSubSettings()
    {
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [
            'ext_key_1' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0.' => ['key_1' => 'value_1_b'],
                '1.' => ['key_1' => 'value_1_c']
            ]
        ], true);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1_b', 'key_2' => 'value_2'],
            1 => ['key_1' => 'value_1_c', 'key_2' => 'value_2'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadSettingsWithOverwrite()
    {
        ObjectAccess::setProperty($this->subject, 'overwriteSettingsRaw', [
            'ext_key_1' => ['key_2' => 'value_2_b']
        ], true);
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [
            'ext_key_1' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
            ]
        ], true);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1', 'key_2' => 'value_2_b'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadSubSettingsWithOverwrite()
    {
        ObjectAccess::setProperty($this->subject, 'overwriteSettingsRaw', [
            'ext_key_1' => [
                'key_2' => 'value_2_b',
                '0.' => ['key_1' => 'value_1_d'],
                '1.' => [],
            ]
        ], true);
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [
            'ext_key_1' => [
                'key_1' => 'value_1',
                'key_2' => 'value_2',
                '0.' => ['key_1' => 'value_1_b'],
                '1.' => ['key_1' => 'value_1_c']
            ]
        ], true);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([
            0 => ['key_1' => 'value_1_d', 'key_2' => 'value_2_b'],
            1 => ['key_1' => 'value_1_c', 'key_2' => 'value_2_b'],
        ], $result);
    }

    /**
     * @test
     */
    public function loadNonScalarSettings()
    {
        ObjectAccess::setProperty($this->subject, 'overwriteSettingsRaw', [], true);
        ObjectAccess::setProperty($this->subject, 'extSettingsRaw', [
            'ext_key_1' => ['some_array_key.' => ['some_key' => 'some_value']]
        ], true);
        $result = $this->subject->getFormrelaySettings('ext_key_1');
        $this->assertEquals([0 => ['some_array_key.' => ['some_key' => 'some_value']]], $result);
    }
}
