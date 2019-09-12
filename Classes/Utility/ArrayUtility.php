<?php

namespace Mediatis\Formrelay\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility as CoreArrayUtility;

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
     * Crawls recursively through the array, finds every key "<key>" whose value is "__UNSET"
     * and then deletes array entries "<key>" and "<key>."
     *
     * @param array $data
     */
    protected static function resolveUnsetFeature(array &$data)
    {
        foreach ($data as $key => $_) {
            if (is_array($data[$key])) {
                static::resolveUnsetFeature($data[$key]);
            } elseif ($data[$key] === '__UNSET') {
                unset($data[$key]);
                unset($data[$key . '.']);
            }
        }
    }

    /**
     * Based on mergeRecursiveWithOverrule, but for plain arrays
     * - plain arrays are converted to typoScript arrays, then merged, then converted back to plain arrays
     * - changes the unset feature so that "key" => "__UNSET" also unsets "key."
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
        CoreArrayUtility::mergeRecursiveWithOverrule($tsOriginal, $tsOverrule, $addKeys, $includeEmptyValues, false);
        if ($enableUnsetFeature) {
            static::resolveUnsetFeature($tsOriginal);
        }
        $original = static::convertTypoScriptArrayToPlainArray($tsOriginal);
    }
}
