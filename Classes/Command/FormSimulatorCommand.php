<?php

namespace Mediatis\Formrelay\Command;

use Mediatis\Formrelay\Exceptions\InvalidXmlException;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use Mediatis\Formrelay\Service\FormSimulatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
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

    protected $mandatoryOptions = [];

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Re-send form submits from the XML log to destinations.');
        $this->setHelp($this->getDescription());
        $this->addUsage('--filePath=<filePath> --configPath=<configPath> [--delay=<delay>]');
        $this->addOption('filePath', null, InputOption::VALUE_REQUIRED, 'Path to file to import from');
        $this->addOption('configPath', null, InputOption::VALUE_REQUIRED, 'Path to to file with TypoScript configuration');
        $this->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Delay between two submissions');
        $this->mandatoryOptions['filePath'] = 1591274665;
        $this->mandatoryOptions['configPath'] = 1591293268;
    }

    /**
     * Re-sends form data from a given log file.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @cli
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->formSimulatorService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->formSimulatorService = $objectManager->get(FormSimulatorService::class);
        }

        foreach ($this->mandatoryOptions as $optionName => $errorCode) {
            if ($input->getOption($optionName) === null) {
                $output->writeln($this->getDescription());
                $output->writeln('Option "--' . $optionName . '" is missing');
                $output->writeln($this->getUsages());
                return $errorCode;
            }
        }

        try {
            $message = $this->formSimulatorService->run(
                $input->getOption('filePath'),
                $input->getOption('configPath'),
                (int)$input->getOption('delay') ?? 20
            );
            $result = 0;
        } catch (InvalidXmlFileException $e) {
            $message = $e->getMessage();
            $result = 1591271675;
        } catch (ServiceUnavailableException $e) {
            $message = $e->getMessage();
            $result = 1591271712;
        } catch (InvalidXmlException $e) {
            $message = $e->getMessage();
            $result = 1591271756;
        } catch (InvalidFileException $e) {
            $message = $e->getMessage();
            $result = 1591293814;
        }

        $output->writeln($message);
        return $result;
    }
}
