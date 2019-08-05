<?php

namespace Mediatis\Formrelay\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Michael Vöhringer (Mediatis AG) <voehringer@mediatis.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Mediatis\Formrelay\Utility\FormrelayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Mediatis\Formrelay\DataProcessorInterface;
use Mediatis\Formrelay\DataProviderInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class FormrelayManager implements SingletonInterface
{

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings;

    public function injectConfigurationManager(ConfigurationManager $configurationManager) {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param array $data The original field array
     * @param bool|array $formSettings
     * @param bool $simulate
     * @param bool|array $attachments paths to processed user uploads
     */
    public function process($data, $formSettings = false, $simulate = false, $attachments = false)
    {
        if (!$this->settings) {
            $typoScript = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
            $this->settings = $typoScript['settings.'];
        }

        if (!$simulate) {
            $this->getAdditionalData($data);
        }
        $this->logData($data);
        $this->callPlugins($data, $formSettings, $attachments);
    }

    private function getAdditionalData(&$data)
    {
        // Add Additional Data
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'])) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'] as $classReference) {
                $dataProvider = $objectManager->get($classReference);

                if ($dataProvider instanceof DataProviderInterface) {
                    $dataProvider->addData($data);
                } else {
                    throw new \InvalidArgumentException(
                        'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\DataProviderInterface.',
                        1359156192
                    );
                }
            }
        }
    }

    private function logData($data = false, $error = false)
    {
        $logfileBase = $this->settings['logfile.']['basePath'];

        // Only write a logfile if path is set in TS Config and logdata is not empty
        if (strlen($logfileBase) > 0) {
            $logfilePath = $logfileBase . DIRECTORY_SEPARATOR . $this->settings['logfile.']['system'] . '.xml';

            $xmlLog = simplexml_load_string("<?xml version=\"1.0\" encoding=\"UTF-8\"?><log />");
            $xmlLog->addAttribute('type', $error ? 'error' : 'notice');
            $xmlLog->addChild('logdate', date('r'));
            $xmlLog->addChild('userIP', \Mediatis\Formrelay\Utility\IpAddress::getUserIpAdress());

            if ($data) {
                $xmlFields = $xmlLog->addChild('form');
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $xmlField = $xmlFields->addChild('field', FormrelayUtility::xmlentities($value));
                    $xmlField->addAttribute('name', FormrelayUtility::xmlentities($key));
                }
            }

            $logdata = $xmlLog->asXML();

            // open logfile and place cursor at the end of file
            if ($logfile = fopen($logfilePath, "a")) {
                // write xml to logfile and close it
                @fwrite($logfile, $logdata);
                fclose($logfile);
            } else {
                if (!is_writable($logfilePath)) {
                    GeneralUtility::devLog("logfile is not writeable", __CLASS__, 0, $logfilePath);
                }
                GeneralUtility::devLog("error: ", __CLASS__, 0, error_get_last());
            }
        }
    }

    /**
     * call all configures subplugins to process the data
     * @param  array &$data All the data as key->value array
     * @param  array $formSettings setting of formrelay
     * @param bool|array $attachments paths of processed uploads
     */
    private function callPlugins(&$data, $formSettings, $attachments = false)
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProcessor'])) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProcessor'] as $classReference) {
                $dataHook = $objectManager->get($classReference);
                $this->configurationManager->setOverwriteSettings($formSettings);
                if ($dataHook instanceof DataProcessorInterface) {
                    $dataHook->processData($data, $attachments);
                } else {
                    throw new \InvalidArgumentException(
                        'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\DataProcessorInterface.',
                        1359156192
                    );
                }
            }
        }
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
