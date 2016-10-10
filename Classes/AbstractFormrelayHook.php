<?php
namespace Mediatis\Formrelay;
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Michael Vöhringer (mediatis AG) <voehringer@mediatis.de>
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

/**
 * Plugin Send form data to SourceFoce.com
 *
 * @author	Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package	TYPO3
 * @subpackage	leica_sendform
 */
abstract class AbstractFormrelayHook {

	protected $conf;

	protected $overwriteTsKey = NULL;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct($overwriteTsKey = NULL) {
		$this->setOverwriteTsKey($overwriteTsKey);
	}

	public function setOverwriteTsKey($overwriteTsKey) {
		$this->overwriteTsKey = $overwriteTsKey;
		GeneralUtility::devLog('AbstractFormrelayHook::getTsKey', __CLASS__, 0, $this->getTsKey());
		$this->conf = FormrelayUtility::loadPluginTS($this->getTsKey(), $this->overwriteTsKey);
	}

	abstract protected function getTsKey();

	/**
	 * Determines via the TypoScript structure fields.validation whether to send the data or do nothing.
	 * @param  array $data form fields against which shall be validated
	 * @return boolean true if the validation succeeded, otherwise false
	 */
	protected function validateForm($data, $conf = null, $confsPassed = null) {

		if ($conf === null) {
			$conf = $this->conf;
		}
		if ($confsPassed === null) {
			$confsPassed = array($this->getTsKey());
		}

		// validate required fields
		if (trim($conf['fields.']['validation.']['required.'])) {
			$requiredFields = explode(',', trim($conf['fields.']['validation.']['required.']));
			foreach ($requiredFields as $requiredField) {
				if (!isset($data[$requiredField])) {
					GeneralUtility::devLog('validateForm - Required field not set '. $requiredField, __CLASS__, 0);
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
				foreach (array('whitelist.' => false, 'blacklist.' => true) as $filterType => $negateFilterRule) {
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
				foreach (array('equals' => false, 'equalsNot' => true) as $filterType => $negateFilterRule) {
					if (isset($filter[$filterType])) {
						$validationKeys = explode(',', $filter[$filterType]);
						foreach ($validationKeys as $validationKey) {
							if (in_array($validationKey, $confsPassed)) { // prevent loops
								$filterMatched = false;
								break;
							}
							$externalConf = FormrelayUtility::loadPluginTS($validationKey, $this->overwriteTsKey);
							if (!$externalConf) {
								$filterMatched = false;
								break;
							}
							$externalValidation = $this->validateForm($data, $externalConf, array_merge($confsPassed, array($validationKey)));
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
				GeneralUtility::devLog('validateForm - !filterMatched '. $requiredField, __CLASS__, 0);
				return false;
			}
		} else  {
			if (!$conf['fields.']['validation.']['validWithNoFilters']) {
				GeneralUtility::devLog('validateForm - !validWithNoFilters '. $requiredField, __CLASS__, 0);
				return false;
			}
		}

		// all validation passed
		return true;
	}

	/**
	 * Processes the whole mapping algorithm for one field
	 * @param  array  $result      The result array where the mapping will be stored
	 * @param  string $key         The original key of the field
	 * @param  string $mappedValue The mapped value of the field
	 * @param  string $mappedKey   The mapped key of the field
	 */
	protected function processField(&$result, $key, $mappedValue, $mappedKey) {

		$keyPrefixIndex = strpos($mappedKey, ':');
		$keyPrefix = false;
		if ($keyPrefixIndex !== FALSE && $keyPrefixIndex > 0) {
			$keyPrefix = substr($mappedKey, 0, $keyPrefixIndex);
			$mappedKey = substr($mappedKey, $keyPrefixIndex + 1);
		}

		switch ($keyPrefix) {
			case 'passthrough':
				// just pass the key-value-pair as it is
				// useful for the rule mappingOther which applies to all fields which don't have a mapping at all
				// example:
				// mappingOther = passthrough:
				// key = 'foo'; value = 'bar'
				// result = array('foo' => 'bar');
				$result[$key] = $mappedValue;
			break;
			case 'split':
				// explode the value using the space char as separator, split the result to the given fields
				// example:
				// mapping = 'split:first_name,last_name'
				// value = 'John Doe' => result = array('first_name' => 'John', 'last_name' => 'Doe');
				// value = 'John' => result = array('first_name' => 'John');
				// value = 'John Doe Smith' => result = array('first_name' => 'John', 'last_name' => 'Doe Smith');
				$valueSeparator = ' ';
				$splitToFields = explode(',', $mappedKey);
				$splittedValues = explode($valueSeparator, $mappedValue);
				while (count($splitToFields) > 1 && count($splittedValues) > 0) {
					// split for all fields but the last
					$splittedField = array_shift($splitToFields);
					$splittedValue = array_shift($splittedValues);
					$this->processField($result, $key, $splittedValue, $splittedField);
				}
				if (count($splittedValues) > 0) {
					// concat the remaining splitted values again and use them for the last field
					$splittedField = array_shift($splitToFields);
					$splittedValue = implode($valueSeparator, $splittedValues);
					$this->processField($result, $key, $splittedValue, $splittedField);
				}
			break;

			case 'fields':
				// share the value with multiple fields
				// example:
				// mapping = 'fields:country,country_code'
				// value = 'US' => result = array('country' => 'US', 'country_code' => 'US');
				$sharedKeys = explode(',', $mappedKey);
				foreach ($sharedKeys as $sharedKey) {
					$this->processField($result, $key, $mappedValue, $sharedKey);
				}
			break;

			case 'negate':
				// write the negated value into the given field
				// example:
				// mapping = 'negate:emailOptOut'
				// value = '1' => result = array('emailOptOut' => 0)
				// value = '0' => result = array('emailOptOut' => 1)
				// value = 'foobar' => result = array('emailOptOut' => 0)
				$this->processField($result, $key, $mappedValue ? 0 : 1, $mappedKey);
			break;

			case 'concat':
				// concat the key-value-pair into one field (along with other pairs)
				// example:
				// mapping = 'concat:description'
				// key = 'foo'; value = 'bar'
				// followed by key = 'oof'; value = 'baz'
				// result = array('description' => 'foo = bar
				// oof = baz
				// ');
				if (!isset($result[$mappedKey])) { $result[$mappedKey] = ''; }
				$result[$mappedKey] .= $key . ' = ' . $mappedValue . PHP_EOL;
			break;

			case 'append':
				// appends the values into one field (along with other values)
				// example:
				// mapping = 'append:description'
				// key = 'foo'; value = 'bar'
				// followed by key = 'oof'; value = 'baz'
				// result = array('description' => 'bar
				// baz
				// ');
				if (!isset($result[$mappedKey])) { $result[$mappedKey] = ''; }
				$result[$mappedKey] .= $mappedValue . PHP_EOL;
			break;

			default:
				// just use the key and value as key and value
				$result[$mappedKey] = $mappedValue;
			break;
		}
	}

	/**
	 * Builds the whole mapping array for all form fields.
	 * @param  array $data The original field array
	 * @return array The array with the mapped fields and values
	 */
	protected function processAllFields($data) {
		$result = isset($this->conf['fields.']['defaults.']) ? $this->conf['fields.']['defaults.'] : array();

		$fieldMapping = $this->conf['fields.']['mapping.'];

		if (isset($this->conf['fields.']['specialMapping.'])) {
			foreach ($this->conf['fields.']['specialMapping.'] as $fieldWithPostfix => $mappingData) {
				$field = substr($fieldWithPostfix, 0, -1);
				if (isset($mappingData['values'])) {
					$valueListString = $mappingData['values'];
					$valueList = trim($valueListString) ? explode(',', strtolower(trim($valueListString))) : array('');
					$currentValue = trim($data[$field]) ? strtolower(trim($data[$field])) : '';
					if (!in_array($currentValue, $valueList)) {
						continue;
					}
				}
				if (isset($mappingData['valuesNot'])) {
					$valueListString = $mappingData['valuesNot'];
					$valueList = trim($valueListString) ? explode(',', strtolower(trim($valueListString))) : array('');
					$currentValue = trim($data[$field]) ? strtolower(trim($data[$field])) : '';
					if (in_array($currentValue, $valueList)) {
						continue;
					}
				}
				foreach ($mappingData['mapping.'] as $mappingField => $mappingRule) {
					$fieldMapping[$mappingField] = $mappingRule;
				}

			}
		}

		$fieldMappingOther = $this->conf['fields.']['mappingOther'];

		$valueMapping = $this->conf['fields.']['values.']['mapping.'];

		$ignoreEmptyFields = $this->conf['fields.']['values.']['ignoreIfEmpty'];
		$ignoreKeyString = trim(strtolower($this->conf['fields.']['ignore']));
		$ignoreKeys = $ignoreKeyString ? explode(',', $ignoreKeyString) : array();

		foreach ($data as $key => $value) {

			// ignore empty values (mostly hidden fields)
			if ($ignoreEmptyFields && !trim($value)) { continue; }

			if ($this->conf['fields.']['removeFieldNameParts']) {
				//t3lib_div::devLog('removeFieldNameParts: "' . $this->conf['removeFieldNameParts'] . '"', 'leica_sendform');
				$patterns = explode(',', $this->conf['fields.']['removeFieldNameParts']);
				foreach ($patterns as $pattern) {
					$key = preg_replace($pattern, '', $key);
				}
			}

			// ignore superfluous meta data
			if (in_array($key, $ignoreKeys)) { continue; }

			if (is_array($value)) {
				$value = implode(',', $value);
			}

			// if a value mapping exists, use it
			$mappedValue = $value;
			if (isset($valueMapping[$key . '.']) && isset($valueMapping[$key . '.'][$value])) {
				$mappedValue = $valueMapping[$key . '.'][$value];
			}

			// if there is no mapping for the key, use the other-key
			$mappedKey = isset($fieldMapping[$key]) ? $fieldMapping[$key] : $fieldMappingOther;

			$this->processField($result, $key, $mappedValue, $mappedKey);
		}

		return $result;
	}
}