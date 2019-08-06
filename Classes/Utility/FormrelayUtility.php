<?php

namespace Mediatis\Formrelay\Utility;

use InvalidArgumentException;
use Mediatis\Formrelay\DataProvider\DataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use Mediatis\Formrelay\FormrelayExtensionInterface;
use Mediatis\Formrelay\Service\FormrelayManager;

final class FormrelayUtility
{
    public static function xmlentities($string)
    {
        return str_replace('&#039;', '&apos;', htmlspecialchars(self::convertToUtf8($string), ENT_QUOTES, 'UTF-8'));
    }

    public static function convertToUtf8($content)
    {
        if (!mb_check_encoding($content, 'UTF-8') || !($content === mb_convert_encoding(
                    mb_convert_encoding($content, 'UTF-32', 'UTF-8'),
                    'UTF-8',
                    'UTF-32'
                ))
        ) {
            $content = mb_convert_encoding($content, 'UTF-8');
        }
        return $content;
    }

    /**
     * Flattens a list of sub structures of key-value pairs to a list of flat key-value arrays.
     * Example
     * Input: array('abc' => array( '10' => array('key' => 'foo', 'value' => 'bar'), 'baz' => 'snafu'), 'cde' => array('10' => array('key' => 'x', 'value' => 'y'), 20 => array('key' => 'g', 'value' => 'h')))
     * Output: array('abc' => array('foo' => 'bar', 'baz' => 'snafu'), 'efg' => array('x' => 'y', 'g' => 'h'))
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string $multipleKeySeparator
     * @return array
     *
     * @see flattenKeyValuesSubArray
     */
    public static function flattenKeyValueSubArrayList($array, $key = 'key', $value = 'value', $multipleKeySeparator = ',')
    {
        $result = [];
        foreach ($array as $k => $v) {
            $result[$k] = self::flattenKeyValueSubArray($v, $key, $value, $multipleKeySeparator);
        }
        return $result;
    }

    /**
     * Flattens a sub structure of key-value pairs to a flat key-value array.
     * Example
     * Input: array( '10' => array('key' => 'foo', 'value' => 'bar'), 'baz' => 'snafu')
     * Output: array('foo' => 'bar', 'baz' => 'snafu')
     *
     * @param array $array The key-value pairs that need to be flattened
     * @param string $key The name of the key field in the pair
     * @param string $value The name of the value field in the pair
     * @param string $multipleKeySeparator If not false, it is the separator for the key field having multiple keys. If false, multiple keys are forbidden.
     * @return array
     */
    public static function flattenKeyValueSubArray($array, $key = 'key', $value = 'value', $multipleKeySeparator = ',')
    {
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v) && isset($v[$key]) && isset($v[$value])) {
                if ($multipleKeySeparator) {
                    $multiKeys = explode($multipleKeySeparator, $v[$key]);
                    foreach ($multiKeys as $multiKey) {
                        $result[$multiKey] = $v[$value];
                    }
                } else {
                    $result[$v[$key]] = $v[$value];
                }
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    public static function buildFieldList($tsFieldArray) {
        $fieldsDone = [];
        $fields = [];
        foreach ($tsFieldArray as $key => $value) {
            if (substr($key, -1) === '.') {
                $key = substr($key, 0, -1);
            }
            if ($fieldsDone[$key]) { continue; }
            $fieldsDone[$key] = true;
            $fields[] = [
                'name' => $tsFieldArray[$key] ?: '',
                'config' => $tsFieldArray[$key . '.'] ?: []
            ];
        }
        return $fields;
    }

    public static function parseSeparatorString($str) {
        $str = str_replace('\\n', PHP_EOL, trim($str));
        $str = str_replace('\\s', ' ', $str);
        return $str;
    }

    public static function registerExtension(string $classReference)
    {
        if (!class_implements($classReference, FormrelayExtensionInterface::class)) {
            throw new InvalidArgumentException(
                'Error detector "' . $classReference . '" must implement interface ' . FormrelayExtensionInterface::class . '.',
                1565086200
            );

        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $signalSlotDispatcher = $objectManager->get(Dispatcher::class);

        $signals = [
            FormrelayManager::SIGNAL_REGISTER_EXTENSION,
            FormrelayManager::SIGNAL_BEFORE_PERMISSION_CHECK,
            FormrelayManager::SIGNAL_AFTER_PERMISSION_CHECK,
            FormrelayManager::SIGNAL_BEFORE_DATA_MAPPING,
            FormrelayManager::SIGNAL_AFTER_DATA_MAPPING,
            FormrelayManager::SIGNAL_DISPATCH,
        ];
        foreach ($signals as $signal) {
            $signalSlotDispatcher->connect(
                FormRelayManager::class, // Signal class name
                $signal,                 // Signal name
                $classReference,         // Slot class name
                $signal                  // Slot name
            );
        }
    }

    public static function registerDataProvider(string $classReference)
    {
        if (!class_implements($classReference, DataProviderInterface::class)) {
            throw new InvalidArgumentException(
                'Error detector "' . $classReference . '" must implement interface ' . DataProviderInterface::class . '.',
                1565087714
            );
        }
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $signalSlotDispatcher = $objectManager->get(Dispatcher::class);
        $signalSlotDispatcher->connect(
            FormrelayManager::class,           // Signal class name
            FormrelayManager::SIGNAL_ADD_DATA, // Signal name
            $classReference,                   // Slot class name
            FormrelayManager::SIGNAL_ADD_DATA  // Slot name
        );
    }
}
