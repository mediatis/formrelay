<?php

namespace Mediatis\Formrelay\Utility;

use FormRelay\Core\Exception\FormRelayException;
use FormRelay\Core\Model\Submission\SubmissionConfigurationInterface;

final class ArrayUtility
{
    public static function convertConfigurationForRelayFormat(array $setup)
    {
        $keys = array_keys($setup);
        if (in_array(SubmissionConfigurationInterface::KEY_SELF, $keys, true)) {
            throw new FormRelayException('TypoScript array not compatible with form-relay/core');
        }
        foreach ($keys as $key) {
            if ($key === '_typoScriptNodeValue') {
                $setup[SubmissionConfigurationInterface::KEY_SELF] = $setup[$key];
                unset($setup[$key]);
            } else {
                $value = $setup[$key];
                if ($value === '__UNSET') {
                    $setup[$key] = null;
                } elseif (is_array($value)) {
                    $setup[$key] = static::convertConfigurationForRelayFormat($value);
                }
            }
        }
        return $setup;
    }

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
}
