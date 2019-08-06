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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\SingletonInterface;
use Mediatis\Formrelay\Service\ConfigurationManager;

/**
 * Decides whether or not a data set shall be sent to a specific end point.
 *
 * @author  Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay
 */
class FormrelayGate implements SingletonInterface
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    public function injectConfigurationManager(ConfigurationManager $configurationManager) {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Determines via the TypoScript structure fields.gate whether to send the data or not.
     * @param array $data form fields which shall be checked against config
     * @param string $extKey key of the extension which has to be checked
     * @param int $index The index of the instance of the extension which has to be checked
     * @param array $confsPassed Array of keys of configurations which already have been checked (to avoid recursion loops)
     * @return boolean true if the permission is given, otherwise false
     */
    public function checkPermission(array $data, string $extKey, int $index, array $confsPassed = [])
    {
        $conf = $this->configurationManager->getFormrelaySettings($extKey, $index);

        if (empty($confsPassed)) {
            array_push($confsPassed, $extKey);
        }

        if (!$conf['enabled']) {
            return false;
        }

        // check required fields
        if (trim($conf['fields.']['gate.']['required'])) {
            $requiredFields = explode(',', trim($conf['fields.']['gate.']['required']));
            foreach ($requiredFields as $requiredField) {
                if (!isset($data[$requiredField])) {
                    GeneralUtility::devLog('permit - Required field not set ' . $requiredField, __CLASS__, 0);
                    return false;
                }
            }
        }

        // check filter rules
        $filterFound = false;
        $filterMatched = false;
        if (isset($conf['fields.']['gate.']['filters.'])) {
            foreach ($conf['fields.']['gate.']['filters.'] as $filterIndex => $filter) {
                $filterFound = true;
                $filterMatched = true;

                // include and exclude lists
                foreach (['whitelist.' => false, 'blacklist.' => true] as $filterType => $negateFilterRule) {
                    if (isset($filter[$filterType])) {
                        foreach ($filter[$filterType] as $field => $valueList) {
                            if (!isset($data[$field])) {
                                if ($negateFilterRule) {
                                    continue;
                                } else {
                                    $filterMatched = false;
                                    break;
                                }
                            }
                            $valueList = explode(',', strtolower($valueList));
                            $currentValue = strtolower($data[$field]);
                            $valueInList = in_array($currentValue, $valueList);
                            if (($negateFilterRule && $valueInList) || (!$negateFilterRule && !$valueInList)) {
                                $filterMatched = false;
                                break;
                            }
                        }
                        if (!$filterMatched) {
                            break;
                        }
                    }
                }

                // external permission checks
                foreach (['equals' => false, 'equalsNot' => true] as $filterType => $negateFilterRule) {
                    if (isset($filter[$filterType])) {
                        $externalKeys = explode(',', $filter[$filterType]);
                        foreach ($externalKeys as $externalKey) {
                            if (in_array($externalKey, $confsPassed)) { // prevent loops
                                $filterMatched = false;
                                break;
                            }
                            $confsPassed = array_merge($confsPassed, [$externalKey]);
                            $externalPermission = false;
                            for ($externalConfIndex = 0; $externalConfIndex < $this->configurationManager->getFormrelaySettingsCount($externalKey); $externalConfIndex++) {
                                if ($this->checkPermission($data, $externalKey, $externalConfIndex, $confsPassed)) {
                                    $externalPermission = true;
                                    break;
                                }
                            }
                            if ((!$negateFilterRule && !$externalPermission) || ($negateFilterRule && $externalPermission)) {
                                $filterMatched = false;
                                break;
                            }
                        }
                        if (!$filterMatched) {
                            break;
                        }
                    }
                }

                // filters are disjunctive, if any gives permission, then the check complete
                if ($filterMatched) {
                    break;
                }
            }
        }
        if ($filterFound) {
            if (!$filterMatched) {
                GeneralUtility::devLog('permit - !filterMatched ', __CLASS__, 0);
                return false;
            }
        } else {
            if (!$conf['fields.']['gate.']['validWithNoFilters']) {
                GeneralUtility::devLog('permit - !validWithNoFilters ', __CLASS__, 0);
                return false;
            }
        }

        // all checks passed
        return true;
    }
}
