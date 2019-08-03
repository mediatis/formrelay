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

/**
 * Plugin Send form data to SourceFoce.com
 *
 * @author  Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay
 */
class DataMapper
{
    /**
     * Builds the whole mapping array for all form fields.
     * @param  array $data The original field array
     * @param array $conf The configuration to be used
     * @return array The array with the mapped fields and values
     */
    public function processAllFields($data, $conf)
    {
        $result = isset($conf['fields.']['defaults.']) ? $conf['fields.']['defaults.'] : [];

        $fieldMapping = $conf['fields.']['mapping.'];

        if (isset($conf['fields.']['specialMapping.'])) {
            foreach ($conf['fields.']['specialMapping.'] as $fieldWithPostfix => $mappingData) {
                $field = substr($fieldWithPostfix, 0, -1);
                if (isset($mappingData['values'])) {
                    $valueListString = $mappingData['values'];
                    $valueList = trim($valueListString) ? explode(',', strtolower(trim($valueListString))) : [''];
                    $currentValue = trim($data[$field]) ? strtolower(trim($data[$field])) : '';
                    if (!in_array($currentValue, $valueList)) {
                        continue;
                    }
                }
                if (isset($mappingData['valuesNot'])) {
                    $valueListString = $mappingData['valuesNot'];
                    $valueList = trim($valueListString) ? explode(',', strtolower(trim($valueListString))) : [''];
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

        $fieldMappingOther = $conf['fields.']['mappingOther'];
        $fieldMappingOtherConfig = $conf['fields.']['mappingOther.'] ?: [];

        $valueMapping = [];
        if (isset($conf['fields.']['values.']['mapping.'])) {
            $valueMapping = FormrelayUtility::flattenKeyValueSubArrayList(
                $conf['fields.']['values.']['mapping.'],
                'trigger',
                'value'
            );
        }

        $ignoreEmptyFields = $conf['fields.']['values.']['ignoreIfEmpty'];
        $ignoreKeyString = trim(strtolower($conf['fields.']['ignore.']['value']));
        $ignoreKeys = $ignoreKeyString ? explode(',', $ignoreKeyString) : [];

        foreach ($data as $key => $value) {
            $key = strtolower($key);

            // ignore empty values (mostly hidden fields)
            if ($ignoreEmptyFields && trim($value) === '') {
                continue;
            }

            if ($conf['fields.']['removeFieldNameParts']) {
                $patterns = explode(',', $conf['fields.']['removeFieldNameParts']);
                foreach ($patterns as $pattern) {
                    $key = preg_replace($pattern, '', $key);
                }
            }

            // ignore superfluous meta data
            if (in_array($key, $ignoreKeys)) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $mappedValue = $value;

            if (isset($valueMapping[$key . '.'])) {
                $mappedValue = $this->processValue($mappedValue, $valueMapping, $key, $data);
            }

            $mappedKey = $fieldMappingOther;
            $mappedKeyConfig = $fieldMappingOtherConfig;
            if (isset($fieldMapping[$key]) || isset($fieldMapping[$key . '.'])) {
                $mappedKey = $fieldMapping[$key] ?: '';
                $mappedKeyConfig = $fieldMapping[$key . '.'] ?: [];
            }

            $this->processField($result, $key, $mappedValue, $mappedKey, $mappedKeyConfig);
        }
        return $result;
    }

    /**
     * @param $mappedValue
     * @param $valueMapping
     * @param $key
     * @param $data
     * @return mixed
     */
    protected function processValue(&$mappedValue, $valueMapping, $key, &$data)
    {
        // FormFieldMultiValue mapping
        if ($mappedValue instanceof FormFieldMultiValue) {
            $resultMappedValue = new FormFieldMultiValue();
            foreach ($mappedValue as $originalKey => $originalValue) {
                $resultMappedValue[$originalKey] = $this->processValue($originalValue, $valueMapping, $key, $data);
            }
            $mappedValue = $resultMappedValue;
            // Conditionan value mapping
        } elseif (is_array($valueMapping[$key . '.'][$mappedValue . '.'])) {
            foreach ($valueMapping[$key . '.'][$mappedValue . '.'] as $condition => $conditionParams) {
                $mappedValue = $this->processValueCondition($mappedValue, $condition, $conditionParams, $key, $data);
            }
            // Straight value mapping
        } elseif (isset($valueMapping[$key . '.'][$mappedValue])) {
            $mappedValue = $valueMapping[$key . '.'][$mappedValue];
        }
        return $mappedValue;
    }

    /**
     * @param $mappedValue
     * @param $condition
     * @param array $conditionParams
     * @param $key
     * @param $data
     * @return mixed
     */
    protected function processValueCondition(&$mappedValue, $condition, array $conditionParams, $key, &$data)
    {
        // Condition parsing for field values. These can be chained
        foreach ($conditionParams as $operator => $operands) {
            switch ($condition) {
                case 'if.':
                    // Map field value depending on the value of another field
                    // example:
                    // fields.values.mapping {
                    //    interesse {                                   # Mapping field
                    //        service {                                 # Mapping value
                    //           if.equals {                            # Condition / Action
                    //               field = inquiry_type_level2        # Foreign field
                    //               value = technical_support_repair   # Foreign field value
                    //               then = Sales Query                 # Then value
                    //               else = Service Support             # Else value
                    //           }}}}
                    switch ($operator) {
                        case 'equals.':
                            $mappedValue = $data[$operands['field']] === $operands['value'] ? $operands['then'] : $operands['else'];
                            break;
                    }
                    break;
            }
        }
        return $mappedValue;
    }

    /**
     * Processes the whole mapping algorithm for one field
     * @param array $result The result array where the mapping will be stored
     * @param string $key The original key of the field
     * @param string $mappedValue The mapped value of the field
     * @param string $mappedKey The mapped key of the field
     * @param array $mappedKeyConfig The configuration for that key
     */
    protected function processField(&$result, $key, $mappedValue, $mappedKey, $mappedKeyConfig)
    {
        if (!$mappedKeyConfig || empty($mappedKeyConfig)) {
            $result[$mappedKey] = $mappedValue;
        } else {
            $ignore = !!$mappedKeyConfig['ignore'];
            $passthrough = !!$mappedKeyConfig['passthrough'];
            $negate = !!$mappedKeyConfig['negate'];
            $split = $mappedKeyConfig['split.'] ?: false;
            $distribute = $mappedKeyConfig['distribute.'] ?: false;
            $join = $mappedKeyConfig['join.'] ?: ($mappedKeyConfig['join'] ? [] : false);
            $appendKeyValue = $mappedKeyConfig['appendKeyValue.'] ?: ($mappedKeyConfig['appendKeyValue'] ? [] : false);
            $appendValue = $mappedKeyConfig['appendValue.'] ?: ($mappedKeyConfig['appendValue'] ? [] : false);
            $ifEmpty = !!$mappedKeyConfig['ifEmpty'];
            $discreteField = !!$mappedKeyConfig['discreteField'];

            if ($ignore) {
                // do nothing
            } elseif ($passthrough) {
                $result[$key] = $mappedValue;
            } else {
                if ($negate) {
                    $mappedValue = !!$mappedValue ? '0' : '1';
                }
                if ($split) {
                    $token = $split['token'] ? FormrelayUtility::parseSeparatorString($split['token']) : ' ';
                    $splitFields = FormrelayUtility::buildFieldList($split['fields.']);
                    $splitValues = explode($token, $mappedValue);
                    while (count($splitFields) > 1 && count($splitValues) > 0) {
                        // split for all fields but the last
                        $splitField = array_shift($splitFields);
                        $splitValue = array_shift($splitValues);
                        $this->processField($result, $key, $splitValue, $splitField['name'], $splitField['config']);
                    }
                    if (count($splitValues) > 0) {
                        // concat the remaining splitted values again and use them for the last field
                        $splitField = array_shift($splitFields);
                        $splitValue = implode($token, $splitValues);
                        $this->processField($result, $key, $splitValue, $splitField['name'], $splitField['config']);
                    }
                } elseif ($distribute) {
                    $sharedFields = FormrelayUtility::buildFieldList($distribute['fields.']);
                    foreach ($sharedFields as $sharedField) {
                        $this->processField($result, $key, $mappedValue, $sharedField['name'], $sharedField['config']);
                    }
                } elseif ($discreteField) {
                    if (!isset($result[$mappedKey]) || !($result[$mappedKey] instanceof FormFieldMultiValueDiscrete)) {
                        $result[$mappedKey] = new FormFieldMultiValueDiscrete([]);
                    }
                    if ($mappedValue instanceof FormFieldMultiValue) {
                        foreach ($mappedValue as $mappedMultiValue) {
                            $result[$mappedKey]->append($mappedMultiValue);
                        }
                    } else {
                        $result[$mappedKey]->append($mappedValue);
                    }
                } else if (!$ifEmpty || !$result[$mappedKey]) {
                    if ($join) {
                        $glue = $join['glue'] ? FormrelayUtility::parseSeparatorString($join['glue']) : PHP_EOL;
                        $result[$mappedKey] = $result[$mappedKey] ?: '';
                        $k = 0;
                        if ($mappedValue instanceof FormFieldMultiValue) {
                            foreach ($mappedValue as $mappedMultiValue) {
                                $result[$mappedKey] .= $mappedMultiValue;
                                $k++;
                                if ($k < count($mappedValue)) {
                                    $result[$mappedKey] .= $glue;
                                }
                            }
                        } else {
                            $result[$mappedKey] = $mappedValue;
                        }
                    } elseif ($appendKeyValue) {
                        $keyValueSeparator = $appendKeyValue['keyValueSeparator']
                            ? FormrelayUtility::parseSeparatorString($appendKeyValue['keyValueSeparator'])
                            : ' = ';
                        $separator = $appendKeyValue['separator']
                            ? FormrelayUtility::parseSeparatorString($appendKeyValue['separator'])
                            : PHP_EOL;
                        if (!isset($result[$mappedKey])) {
                            $result[$mappedKey] = '';
                        }
                        $result[$mappedKey] .= $key . $keyValueSeparator . $mappedValue . $separator;
                    } elseif ($appendValue) {
                        $separator = $appendValue['separator'] ? FormrelayUtility::parseSeparatorString($appendValue['separator']) : PHP_EOL;
                        if (!isset($result[$mappedKey])) {
                            $result[$mappedKey] = $mappedValue;
                        } else {
                            $result[$mappedKey] .= $separator . $mappedValue;
                        }
                    } else {
                        $result[$mappedKey] = $mappedValue;
                    }
                }
            }
        }
    }
}
