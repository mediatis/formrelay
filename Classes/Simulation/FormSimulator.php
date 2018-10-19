<?php

namespace Mediatis\Formrelay\Simulation;

class FormSimulator
{
    const XML_LOG_PREFIX = '<?xml version="1.0" encoding="UTF-8"?>';
    const SUBMISSION_DELAY = 20;

    /**
     * @var \Mediatis\Formrelay\Service\FormrelayManager
     * @inject
     */
    protected $formrelayManager;

    protected $logEntryCounter;

    /**
     * Initializes the TSFE for a given page ID and language.
     *
     * @param   integer The page id to initialize the TSFE for
     * @param   integer System language uid, optional, defaults to 0
     * @param   boolean Use cache to reuse TSFE
     * @return  void
     */
    public static function initializeTsfe($pageId, $language = 0)
    {
        static $tsfeCache = [];

        // resetting, a TSFE instance with data from a different page Id could be set already
        unset($GLOBALS['TSFE']);

        $cacheId = $pageId . '|' . $language;

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TimeTracker\NullTimeTracker::class);
        }

        if (!isset($tsfeCache[$cacheId])) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::_GETset($language, 'L');

            $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'], $pageId, 0);
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->getConfigArray();
        }

        static $hosts = [];
        // relevant for realURL environments, only
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
            $rootpageId = $pageId;
            //$rootpageId = $item->getRootPageUid();
            $hostFound = !empty($hosts[$rootpageId]);

            if (!$hostFound) {
                $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($rootpageId);
                $host = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootline);

                $hosts[$rootpageId] = $host;
            }

            $_SERVER['HTTP_HOST'] = $hosts[$rootpageId];
        }
    }

    protected function computeLogEntry($logEntry)
    {
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($logEntry);
        } catch (\Exception $e) {
            print('ERROR: ' . $e->getMessage() . PHP_EOL);
            return false;
        }

        if (!$xml) {
            foreach (libxml_get_errors() as $error) {
                print('ERROR: ' . $error->message . PHP_EOL);
            }
            return false;
        }

        $formDate = (string)$xml->logdate;

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
        if (count($formData) > 0) {
            print('INFO: re-sending log entry from ' . $formDate . PHP_EOL);
            $this->formrelayManager->process($formData, false, true);
            sleep(self::SUBMISSION_DELAY);
            $this->logEntryCounter++;
        } else {
            print('ERROR: no valid form data found in log entry from ' . $formDate . PHP_EOL);
        }
    }

    protected function computeLogFile($file)
    {
        $this->logEntryCounter = 0;
        $content = file_get_contents($file);
        $index = strrpos($content, self::XML_LOG_PREFIX);
        while ($index !== false) {
            $logEntry = substr($content, $index);

            if (strlen($logEntry) > strlen(self::XML_LOG_PREFIX) + 3) {
                $this->computeLogEntry($logEntry);
            }

            $content = substr($content, 0, $index);
            $index = strrpos($content, self::XML_LOG_PREFIX);
        }
        print('INFO: ' . $this->logEntryCounter . ' log entries re-sent.' . PHP_EOL);
    }


    public function run($file, $pageId = 1)
    {
        // print('FormSimulator::run()' . PHP_EOL);
        // print($file . PHP_EOL);
        // print($pageId . PHP_EOL);
        $this->initializeTsfe($pageId);
        $this->computeLogFile($file);
    }
}
