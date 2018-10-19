<?php

namespace Mediatis\Formrelay\Utility;

final class FormrelayUtility
{
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

    public static function xmlentities($string)
    {
        return str_replace('&#039;', '&apos;', htmlspecialchars(self::convertToUtf8($string), ENT_QUOTES, 'UTF-8'));
    }

    public static function loadPluginTS($extKey, $overwriteKey = null)
    {
        $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$extKey . '.'];
        if (!$conf) {
            $frontendConfigurationManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Extbase\\Configuration\\FrontendConfigurationManager'
            );
            $tsSetup = $frontendConfigurationManager->getTypoScriptSetup();
            $conf = $tsSetup['plugin.'][$extKey . '.'];
        }
        if ($overwriteKey) {
            return $conf['configurationOverwrite.'][$overwriteKey . '.'] ?: $conf;
        }
        return $conf;
    }
}
