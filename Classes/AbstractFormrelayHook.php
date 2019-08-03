<?php

namespace Mediatis\Formrelay;

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

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValueDiscrete;
use Mediatis\Formrelay\Service\DataMapper;
use Mediatis\Formrelay\Service\Gate;
use Mediatis\Formrelay\Utility\FormrelayUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Abstract class for sub-extensions as entry point
 *
 * @author  Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay
 */
abstract class AbstractFormrelayHook
{
    // Configuration to use
    protected $conf;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * Constructor
     *
     * @param DataMapper $dataMapper
     * @param Gate $gate
     * @param Dispatcher $signalSlotDispatcher
     * @return void
     */
    public function __construct(DataMapper $dataMapper, Gate $gate, Dispatcher $signalSlotDispatcher)
    {
        $this->dataMapper = $dataMapper;
        $this->gate = $gate;
        $this->signalSlotDispatcher = $signalSlotDispatcher;
        $this->conf = FormrelayUtility::loadPluginTS($this->getTsKey());
    }

    abstract public function getTsKey();

    /**
     * @param array $data The original field array
     * @param bool|array $formSettings
     * @param bool|array $attachments paths to processed user uploads
     * @return bool
     *
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processData($data, $formSettings = false, $attachments = false)
    {
        if ($formSettings) {
            $ts_formSettings = GeneralUtility::makeInstance(
                'TYPO3\CMS\Extbase\Service\TypoScriptService'
            )->convertPlainArrayToTypoScriptArray($formSettings);
            ArrayUtility::mergeRecursiveWithOverrule($this->conf, $ts_formSettings);
        }

        if (!$this->isEnabled()) {
            return false;
        }

        if (!$this->gate->validateForm($data, $this->conf, [$this->getTsKey()])) {
            return false;
        }

        $data = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'beforeProcessAllFields',
            [$data, $this->getTsKey()]
        )[0];
        $result = $this->dataMapper->processAllFields($data, $this->conf);
        $result = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'afterProcessAllFields',
            [$result, $this->getTsKey()]
        )[0];

        $dispatcher = $this->getDispatcher();
        return $dispatcher->send($result, $attachments);
    }

    abstract protected function isEnabled();

    abstract protected function getDispatcher();
}
