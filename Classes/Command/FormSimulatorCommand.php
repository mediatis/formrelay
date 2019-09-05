<?php

namespace Mediatis\Formrelay\Command;

use Mediatis\Formrelay\Service\FormSimulatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * CLI command for re-sending form submits
 */
class FormSimulatorCommand extends Command
{
    /**
     * FormSimulatorService
     */
    protected $formSimulatorService;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Re-send form submits from the XML log to destinations.');
        $this->setHelp($this->getDescription());
        $this->addUsage('--pageId=<pageId> --filePath=<filePath>');
        $this->addOption('filePath', null, InputOption::VALUE_REQUIRED, 'Path to file to import from');
        $this->addOption('pageId', null, InputOption::VALUE_REQUIRED, 'The page id to initialize the TSFE for.');
    }

    /**
     * Re-sends form data from a given log file.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @cli
     * @throws \Mediatis\Formrelay\Exceptions\InvalidXmlFileException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     * @throws \Mediatis\Formrelay\Exceptions\InvalidXmlException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->formSimulatorService) {
            $this->formSimulatorService = GeneralUtility::makeInstance(ObjectManager::class)->get(FormSimulatorService::class);
        }
        $result = $this->formSimulatorService->run($input->getOption('filePath'), (int)$input->getOption('pageId'));
        $output->writeln($result);
    }
}
