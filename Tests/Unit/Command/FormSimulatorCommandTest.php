<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Test\Unit\Command;

use Mediatis\Formrelay\Command\FormSimulatorCommand;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use Mediatis\Formrelay\Service\FormSimulatorService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class FormSimulatorCommandTest extends UnitTestCase
{
    /** @var CommandTester */
    private $commandTester;

    /**
     * @var MockObject
     */
    private $formSimulatorServiceMock;

    protected function setUp()
    {
        parent::setUp();
        $this->formSimulatorServiceMock = $this->getMockBuilder(FormSimulatorService::class)->setMethods(
            ['run']
        )->getMock();

        $name = 'formrelay:formsimulator';
        $application = new Application();
        $application->add(new FormSimulatorCommand($name));
        $command = $application->find($name);
        ObjectAccess::setProperty($command, 'formSimulatorService', $this->formSimulatorServiceMock, true);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown()
    {
        parent::tearDown();
        GeneralUtility::resetSingletonInstances([]);
        $this->commandTester = null;
    }

    /**
     * @test
     */
    public function executeShouldReturnResultOnSuccessfulExecution()
    {
        $filePath = __DIR__ . '/../../Fixtures/emails.xml';
        $pageId = 1;
        $this->formSimulatorServiceMock->expects($this->once())->method('run')->with(
            $this->equalTo($filePath, $pageId)
        )->willReturn(
            'Success'
        );

        $this->commandTester->execute(['--pageId' => $pageId, '--filePath' => $filePath]);
        $this->assertEquals('Success', trim($this->commandTester->getDisplay()));
    }

    /**
     * @test
     */
    public function executeShouldThrowExceptionIfFileDoesNotExist()
    {
        $filePath = 'idontexist.xml';
        $pageId = 1;

        $this->formSimulatorServiceMock->expects($this->once())->method('run')->with(
            $this->equalTo($filePath, $pageId)
        )->willThrowException(
            new InvalidXmlFileException($filePath)
        );

        $this->expectException(InvalidXmlFileException::class);
        $this->expectExceptionMessage(sprintf('Bad filePath option ("%s")', $filePath));
        $this->commandTester->execute(['--pageId' => $pageId, '--filePath' => $filePath]);
    }
}
