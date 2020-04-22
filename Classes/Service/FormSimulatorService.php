<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Service;

use LibXMLError;
use Mediatis\Formrelay\Exceptions\InvalidXmlException;
use Mediatis\Formrelay\Exceptions\InvalidXmlFileException;
use Mediatis\Formrelay\Utility\FormSimulatorUtility;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

class FormSimulatorService
{
    const XML_LOG_PREFIX = '<?xml version="1.0" encoding="UTF-8"?>';

    /**
     * @var Relay
     */
    protected $relay;

    /**
     * @var int
     */
    protected $logEntryCounter;

    /**
     * @var int
     */
    protected $submitDelay = 20;

    /**
     * @param Relay $relay
     */
    public function injectRelay(Relay $relay)
    {
        $this->relay = $relay;
    }

    /**
     * @param string $file
     * @param int $pageId
     * @return string
     * @throws InvalidXmlException
     * @throws InvalidXmlFileException
     * @throws ServiceUnavailableException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function run(string $file, int $pageId): string
    {
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
                $this->process($formData, $date, $pageId);
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
     * @param int $pageId
     * @throws ServiceUnavailableException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function process(array $formData, string $date, int $pageId)
    {
        if (!empty($formData)) {
            echo 'INFO: re-sending log entry from ' . $date . PHP_EOL;
            $this->initializeTsfe($pageId);
            $this->relay->process($formData, [], true);
            $this->logEntryCounter++;
            sleep($this->submitDelay);
        } else {
            echo 'ERROR: no valid form data found in log entry from ' . $date . PHP_EOL;
        }
    }

    /**
     * Initializes TypoScriptFrontendController for current page and language
     *
     * @param int $pageId
     * @param bool|int $language
     * @param bool $useCache
     * @throws ServiceUnavailableException
     */
    private function initializeTsfe(int $pageId, int $language = 0, bool $useCache = true)
    {
        FormSimulatorUtility::initializeTsfe($pageId, $language, $useCache);
    }
}
