<?php

namespace Mediatis\Formrelay\Utility;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

final class ArrayUtility
{
    /**
     * (copied from TYPO3\CMS\Core\TypoScript\TypoScriptService, not changed, but now it is in a static context)
     *
     * Removes all trailing dots recursively from TS settings array
     *
     * Extbase converts the "classical" TypoScript (with trailing dot) to a format without trailing dot,
     * to be more future-proof and not to have any conflicts with Fluid object accessor syntax.
     *
     * @param array $typoScriptArray The TypoScript array (e.g. array('foo' => 'TEXT', 'foo.' => array('bar' => 'baz')))
     * @return array e.g. array('foo' => array('_typoScriptNodeValue' => 'TEXT', 'bar' => 'baz'))
     */
    public static function convertTypoScriptArrayToPlainArray(array $typoScriptArray): array
    {
        foreach ($typoScriptArray as $key => $value) {
            if (substr((string)$key, -1) === '.') {
                $keyWithoutDot = substr((string)$key, 0, -1);
                $typoScriptNodeValue = isset($typoScriptArray[$keyWithoutDot]) ? $typoScriptArray[$keyWithoutDot] : null;
                if (is_array($value)) {
                    $typoScriptArray[$keyWithoutDot] = static::convertTypoScriptArrayToPlainArray($value);
                    if ($typoScriptNodeValue !== null) {
                        $typoScriptArray[$keyWithoutDot]['_typoScriptNodeValue'] = $typoScriptNodeValue;
                    }
                    unset($typoScriptArray[$key]);
                } else {
                    $typoScriptArray[$keyWithoutDot] = null;
                }
            }
        }
        return $typoScriptArray;
    }

    /**
     * (copied from TYPO3\CMS\Core\TypoScript\TypoScriptService, not changed, but now it is in a static context)
     *
     * Returns an array with Typoscript the old way (with dot).
     *
     * Extbase converts the "classical" TypoScript (with trailing dot) to a format without trailing dot,
     * to be more future-proof and not to have any conflicts with Fluid object accessor syntax.
     * However, if you want to call legacy TypoScript objects, you somehow need the "old" syntax (because this is what TYPO3 is used to).
     * With this method, you can convert the extbase TypoScript to classical TYPO3 TypoScript which is understood by the rest of TYPO3.
     *
     * @param array $plainArray An TypoScript Array with Extbase Syntax (without dot but with _typoScriptNodeValue)
     * @return array array with TypoScript as usual (with dot)
     * @api
     */
    public static function convertPlainArrayToTypoScriptArray(array $plainArray): array
    {
        $typoScriptArray = [];
        foreach ($plainArray as $key => $value) {
            if (is_array($value)) {
                if (isset($value['_typoScriptNodeValue'])) {
                    $typoScriptArray[$key] = $value['_typoScriptNodeValue'];
                    unset($value['_typoScriptNodeValue']);
                }
                $typoScriptArray[$key . '.'] = static::convertPlainArrayToTypoScriptArray($value);
            } else {
                $typoScriptArray[$key] = $value === null ? '' : $value;
            }
        }
        return $typoScriptArray;
    }

    /**
     * (copied from TYPO3\CMS\Core\Utility\ArrayUtility, changed to also unset the sub values of a key ("xyz" and "xyz.")
     *
     * Merges two arrays recursively and "binary safe" (integer keys are
     * overridden as well), overruling similar values in the original array
     * with the values of the overrule array.
     * In case of identical keys, ie. keeping the values of the overrule array.
     *
     * This method takes the original array by reference for speed optimization with large arrays
     *
     * The differences to the existing PHP function array_merge_recursive() are:
     *  * Keys of the original array can be unset via the overrule array. ($enableUnsetFeature)
     *  * Much more control over what is actually merged. ($addKeys, $includeEmptyValues)
     *  * Elements or the original array get overwritten if the same key is present in the overrule array.
     *
     * @param array $original Original array. It will be *modified* by this method and contains the result afterwards!
     * @param array $overrule Overrule array, overruling the original array
     * @param bool $addKeys If set to FALSE, keys that are NOT found in $original will not be set. Thus only existing value can/will be overruled from overrule array.
     * @param bool $includeEmptyValues If set, values from $overrule will overrule if they are empty or zero.
     * @param bool $enableUnsetFeature If set, special values "__UNSET" can be used in the overrule array in order to unset array keys in the original array.
     */
    public static function mergeRecursiveWithOverrule(array &$original, array $overrule, $addKeys = true, $includeEmptyValues = true, $enableUnsetFeature = true)
    {
        foreach ($overrule as $key => $_) {
            if ($enableUnsetFeature && $overrule[$key] === '__UNSET') {
                unset($original[$key]);
                unset($original[$key . '.']); // line added by ude@mediatis.de Sep 2019
                continue;
            }
            if (isset($original[$key]) && is_array($original[$key])) {
                if (is_array($overrule[$key])) {
                    self::mergeRecursiveWithOverrule($original[$key], $overrule[$key], $addKeys, $includeEmptyValues, $enableUnsetFeature);
                }
            } elseif (
                ($addKeys || isset($original[$key])) &&
                ($includeEmptyValues || $overrule[$key])
            ) {
                $original[$key] = $overrule[$key];
            }
        }
        // This line is kept for backward compatibility reasons.
        reset($original);
    }

    /**
     * Like mergeRecursiveWithOverrule, but for plain arrays
     * which are converted to typoScript arrays, then merged, then converted back to plain arrays
     *
     * @see mergeRecursiveWithOverrule
     * @param array $original
     * @param array $overrule
     * @param bool $addKeys
     * @param bool $includeEmptyValues
     * @param bool $enableUnsetFeature
     */
    public static function plainArrayMergeRecursiveWithOverrule(array &$original, array $overrule, $addKeys = true, $includeEmptyValues = true, $enableUnsetFeature = true)
    {
        $tsOriginal = static::convertPlainArrayToTypoScriptArray($original);
        $tsOverrule = static::convertPlainArrayToTypoScriptArray($overrule);
        static::mergeRecursiveWithOverrule($tsOriginal, $tsOverrule, $addKeys, $includeEmptyValues, $enableUnsetFeature);
        $original = static::convertTypoScriptArrayToPlainArray($tsOriginal);
    }
}
