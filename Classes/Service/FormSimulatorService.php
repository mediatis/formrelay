<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Service;

use LibXMLError;
use Mediatis\Formrelay\Configuration\CliConfigurationManager;
use Mediatis\Formrelay\Exceptions\InvalidXmlException;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

class FormSimulatorService
{
    const XML_LOG_PREFIX = '<?xml version="1.0" encoding="UTF-8"?>';

    /** @var TypoScriptParser */
    protected $typoScriptParser;

    /** @var CliConfigurationManager */
    protected $cliConfigurationManager;

    /** @var Relay */
    protected $relay;

    /** @var int */
    protected $logEntryCounter;

    /** @var int */
    protected $submitDelay;

    public function injectTypoScriptParser(TypoScriptParser $typoScriptParser)
    {
        $this->typoScriptParser = $typoScriptParser;
    }

    public function injectCliConfigurationManager(CliConfigurationManager $cliConfigurationManager)
    {
        $this->cliConfigurationManager = $cliConfigurationManager;
    }

    public function injectRelay(Relay $relay)
    {
        $this->relay = $relay;
    }

    protected function processConfigFile(string $configFile)
    {
        if (!file_exists($configFile)) {
            throw new InvalidFileException($configFile);
        }
        $setupString = file_get_contents($configFile);
        $this->typoScriptParser->parse($setupString);
        $setup = ['plugin.' => $this->typoScriptParser->setup];
        $this->cliConfigurationManager->setTypoScriptSetup($setup);
        // TODO is there a better way to  have this implementation injected?
        //      but only from this simulator service, not from a finisher!
        $this->relay->injectFrontendConfigurationManager($this->cliConfigurationManager);
    }

    /**
     * @param string $file
     * @param string $configFile
     * @param int $submitDelay
     * @return string
     * @throws InvalidFileException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidXmlException
     * @throws InvalidXmlFileException
     * @throws ServiceUnavailableException
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function run(string $file, string $configFile, int $submitDelay = 20): string
    {
        $this->processConfigFile($configFile);
        $this->submitDelay = $submitDelay;
        $this->logEntryCounter = 0;
        if (!file_exists($file)) {
            throw new InvalidXmlFileException($file);
        }
        $content = file_get_contents($file);
        $index = strrpos($content, self::XML_LOG_PREFIX);
        // Search for the last occurrence of the XML, parse it, and then remove it.
        while ($index !== false) {
            $logEntry = substr($content, $index);
            if (strlen($logEntry) > strlen(self::XML_LOG_PREFIX) + 3) {
                list($formData, $date) = $this->computeLogEntry($logEntry);
                $this->process($formData, $date);
            }

            $content = substr($content, 0, $index);
            $index = strrpos($content, self::XML_LOG_PREFIX);
        }
        return 'INFO: ' . $this->logEntryCounter . ' log entries re-sent.';
    }

    /**
     * @param string $logEntry
     * @return array
     * @throws InvalidXmlException
     */
    protected function computeLogEntry(string $logEntry): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($logEntry);
        /** @var LibXMLError $e */
        if (empty($xml)) {
            foreach (libxml_get_errors() as $e) {
                throw new InvalidXmlException($logEntry . ' ERROR: ' . $e->message);
            }
        }

        $formDataXml = $xml->form;
        $formData = [];
        foreach ($formDataXml->field as $fieldXml) {
            $fieldName = false;
            foreach ($fieldXml->attributes() as $key => $value) {
                if ($key === 'name') {
                    $fieldName = (string)$value;
                    break;
                }
            }
            if (!$fieldName) {
                continue;
            }
            $fieldValue = (string)$fieldXml;
            $formData[$fieldName] = $fieldValue;
        }
        return [$formData, (string)$xml->logdate];
    }

    /**
     * @param array $formData
     * @param string $date
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws ServiceUnavailableException
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    protected function process(array $formData, string $date)
    {
        if (!empty($formData)) {
            echo 'INFO: re-sending log entry from ' . $date . PHP_EOL;
            $this->relay->process($formData, [], true);
            $this->logEntryCounter++;
            sleep($this->submitDelay);
        } else {
            echo 'ERROR: no valid form data found in log entry from ' . $date . PHP_EOL;
        }
    }
}
