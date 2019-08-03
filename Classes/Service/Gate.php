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

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValueDiscrete;
use Mediatis\Formrelay\Utility\FormrelayUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Decides whether or not a data set shall be sent to a specific end point.
 *
 * @author  Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay
 */
class Gate
{
    /**
     * Determines via the TypoScript structure fields.validation whether to send the data or do nothing.
     * @param  array $data form fields against which shall be validated
     * @param array $conf The configuration of of the current validation
     * @param array $confsPassed Array of keys of configurations which already have been validated (to avoid recursion loops)
     * @return boolean true if the validation succeeded, otherwise false
     */
    public function validateForm($data, $conf, $confsPassed = [])
    {
        // validate required fields
        if (trim($conf['fields.']['validation.']['required'])) {
            $requiredFields = explode(',', trim($conf['fields.']['validation.']['required']));
            foreach ($requiredFields as $requiredField) {
                if (!isset($data[$requiredField])) {
                    GeneralUtility::devLog('validateForm - Required field not set ' . $requiredField, __CLASS__, 0);
                    return false;
                }
            }
        }

        // validate filter rules
        $filterFound = false;
        $filterMatched = false;
        if (isset($conf['fields.']['validation.']['filters.'])) {
            foreach ($conf['fields.']['validation.']['filters.'] as $filterIndex => $filter) {
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

                // external validations
                foreach (['equals' => false, 'equalsNot' => true] as $filterType => $negateFilterRule) {
                    if (isset($filter[$filterType])) {
                        $validationKeys = explode(',', $filter[$filterType]);
                        foreach ($validationKeys as $validationKey) {
                            if (in_array($validationKey, $confsPassed)) { // prevent loops
                                $filterMatched = false;
                                break;
                            }
                            $externalConf = FormrelayUtility::loadPluginTS($validationKey);
                            if (!$externalConf) {
                                $filterMatched = false;
                                break;
                            }
                            $externalValidation = $this->validateForm(
                                $data,
                                $externalConf,
                                array_merge($confsPassed, [$validationKey])
                            );
                            if ((!$negateFilterRule && !$externalValidation) || ($negateFilterRule && $externalValidation)) {
                                $filterMatched = false;
                                break;
                            }
                        }
                        if (!$filterMatched) {
                            break;
                        }
                    }
                }

                // filters are disjunctive, if any is validated, then the validation is complete
                if ($filterMatched) {
                    break;
                }
            }
        }
        if ($filterFound) {
            if (!$filterMatched) {
                GeneralUtility::devLog('validateForm - !filterMatched ', __CLASS__, 0);
                return false;
            }
        } else {
            if (!$conf['fields.']['validation.']['validWithNoFilters']) {
                GeneralUtility::devLog('validateForm - !validWithNoFilters ', __CLASS__, 0);
                return false;
            }
        }

        // all validation passed
        return true;
    }
}
