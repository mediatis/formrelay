<?php
namespace Mediatis\Formrelay\Utility;

final class FormrelayUtility
{

	public static function convertToUtf8($content)
	{
		if(!mb_check_encoding($content, 'UTF-8')
			OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
			$content = mb_convert_encoding($content, 'UTF-8');

			if (mb_check_encoding($content, 'UTF-8')) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Converted to UTF-8', 'extention', 0, $content);
			} else {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Could not converted to UTF-8', 'extention', 0, $content);
			}
		}
		return $content;
	}

	public static function xmlentities($string)
	{
		return str_replace('&#039;', '&apos;', htmlspecialchars(self::convertToUtf8($string), ENT_QUOTES, 'UTF-8'));
	}

	/**
     * Get TypoScript configuration for a specific page
     *
     * @param integer $pageUid: Page id
     * @return array: TS Setup
     */
    public static function loadTS($pageUid)
    {
        $sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_pageSelect');
        $rootLine = $sysPageObj->getRootLine($pageUid);
        $TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_tsparser_ext');
        $TSObj->tt_track = 0;
        $TSObj->init();
        $TSObj->runThroughTemplates($rootLine);
        $TSObj->generateConfig();
        return $TSObj->setup;
	}

    public static function loadPluginTS($extKey, $overwriteKey = NULL)
    {
    	$conf  = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$extKey . '.'];
        if(!$conf) {
            $tsSetup = self::loadTS($GLOBALS['TSFE']->id);
            $conf = $tsSetup['plugin.'][$extKey . '.'];
        }
        if ($overwriteKey) {
        	return $conf['configurationOverwrite.'][$overwriteKey . '.'] ?: $conf;
        }
        return $conf;
    }

}
