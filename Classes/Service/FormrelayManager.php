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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

class FormrelayManager implements SingletonInterface
{
    const SIGNAL_REGISTER_EXTENSION = 'registerExtension';

    const SIGNAL_BEFORE_PERMISSION_CHECK = 'beforePermissionCheck';
    const SIGNAL_AFTER_PERMISSION_CHECK = 'afterPermissionCheck';
    const SIGNAL_BEFORE_DATA_MAPPING = 'beforeDataMapping';
    const SIGNAL_AFTER_DATA_MAPPING = 'afterDataMapping';
    const SIGNAL_DISPATCH = 'dispatch';

    const SIGNAL_ADD_DATA = 'addData';

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var FormrelayGate */
    protected $formrelayGate;

    /** @var DataMapper */
    protected $dataMapper;

    /** @var array */
    protected $settings;

    /**
     * @param array $data The original field array
     * @param bool|array $formSettings
     * @param bool $simulate
     * @param bool|array $attachments paths to processed user uploads
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function process($data, $formSettings = false, $simulate = false, $attachments = false)
    {
        // init objects
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->signalSlotDispatcher = $objectManager->get(Dispatcher::class);
        $this->configurationManager = $objectManager->get(ConfigurationManager::class);
        $this->dataMapper = $objectManager->get(DataMapper::class);
        $this->formrelayGate = $objectManager->get(FormrelayGate::class);

        // register form overwrite settings
        $this->configurationManager->setFormrelaySettingsOverwrite($formSettings);

        // fetch own configuration
        if (!$this->settings) {
            $typoScript = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
            $this->settings = $typoScript['settings.'];
        }

        // call data providers
        if (!$simulate) {
            $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_ADD_DATA, [&$data]);
        }

        // log form submit
        $this->logData($data);

        // call data processor for all extensions
        $extensionList = [];
        $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_REGISTER_EXTENSION, [&$extensionList]);
        foreach ($extensionList as $extKey) {
            $this->processData($data, $extKey, $attachments);
        }
    }

    /**
     * @param array $data The original field array
     * @param string $extKey The key of the extenstion which should be processed next
     * @param bool|array $attachments paths to processed user uploads
     * @return bool
     *
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function processData($data, $extKey, $attachments = false)
    {
        $dispatched = false;
        for ($index = 0; $index < $this->configurationManager->getFormrelaySettingsCount($extKey); $index++) {

            $conf = $this->configurationManager->getFormrelaySettings($extKey, $index);
            $metaData = ['extKey' => $extKey, 'index' => $index, 'config' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => null];

            // check permission
            $metaData = $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_BEFORE_PERMISSION_CHECK, $metaData);
            if ($metaData['result'] === null) {
                $metaData['result'] = $this->formrelayGate->checkPermission($metaData['data'], $metaData['extKey'], $metaData['index']);
            }
            $metaData = $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_AFTER_PERMISSION_CHECK, $metaData);
            if (!$metaData['result']) {
                continue;
            }

            // data mapping
            $metaData['result'] = null;
            $metaData = $this->signalSlotDispatcher->dispatch(__CLASS__,static::SIGNAL_BEFORE_DATA_MAPPING, $metaData);
            if ($metaData['result'] === null) {
                $metaData['data'] = $this->dataMapper->processAllFields($metaData['data'], $metaData['extKey'], $metaData['index']);
            }
            $metaData = $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_AFTER_DATA_MAPPING, $metaData);

            // dispatch
            $metaData['result'] = null;
            $metaData = $this->signalSlotDispatcher->dispatch(__CLASS__, static::SIGNAL_DISPATCH, $metaData);
            if ($metaData['result']) {
                $dispatched = true;
            }
        }
        return $dispatched;
    }

    protected function logData($data = false, $error = false)
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

}

