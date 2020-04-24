<?php

namespace Mediatis\Formrelay\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FormSimulatorUtility
{
    /**
     * Initializes the TSFE for a given page ID and language.
     *
     * @param int $pageId The page id to initialize the TSFE for
     * @param int $language System language uid, optional, defaults to 0
     * @param bool $useCache Use cache to reuse TSFE
     * @return void
     * @throws ServiceUnavailableException
     * @throws ImmediateResponseException
     * @todo When we drop TYPO3 8 support we should use a middleware stack to initialize a TSFE for our needs
     */
    public static function initializeTsfe($pageId, $language = 0, $useCache = true)
    {
        static $tsfeCache = [];

        // resetting, a TSFE instance with data from a different page Id could be set already
        unset($GLOBALS['TSFE']);

        $cacheId = $pageId . '|' . $language;

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance(TimeTracker::class, false);
        }

        GeneralUtility::_GETset($language, 'L');


        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            $pageId,
            0
        );

        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();

        if ($useCache) {
            $tsfeCache[$cacheId] = $GLOBALS['TSFE'];
        }

        static $hosts = [];
        // relevant for realURL environments, only
        if (ExtensionManagementUtility::isLoaded('realurl')) {
            $rootpageId = $pageId;
            $hostFound = !empty($hosts[$rootpageId]);

            if (!$hostFound) {
                $rootline = BackendUtility::BEgetRootLine($rootpageId);
                $host = BackendUtility::firstDomainRecord($rootline);

                $hosts[$rootpageId] = $host;
            }

            $_SERVER['HTTP_HOST'] = $hosts[$rootpageId];
        }

        if ($useCache) {
            $GLOBALS['TSFE'] = $tsfeCache[$cacheId];
            $GLOBALS['TSFE']->settingLocale();
        }
    }
}
