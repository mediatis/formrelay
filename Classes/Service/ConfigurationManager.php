<?php

namespace Mediatis\Formrelay\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Stephan Ude (Mediatis AG) <voehringer@mediatis.de>
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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class ConfigurationManager
 * @package Mediatis\Formrelay\Service
 */
class ConfigurationManager implements SingletonInterface
{
    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var FrontendConfigurationManager
     */
    protected $frontendConfigurationManager;

    protected $extSettingsRaw = [];
    protected $overwriteSettingsRaw = [];
    protected $settings = [];

    public function injectTypoScriptService(TypoScriptService $typoScriptService) {
        $this->typoScriptService = $typoScriptService;
    }

    public function injectFrontendConfigurationManager(FrontendConfigurationManager $frontendConfigurationManager) {
        $this->frontendConfigurationManager = $frontendConfigurationManager;
    }

    public function setOverwriteSettings($overwriteSettings)
    {
        $this->overwriteSettingsRaw = $this->typoScriptService->convertPlainArrayToTypoScriptArray($overwriteSettings);
        $this->settings = [];
    }

    public function getExtensionTypoScriptSetup($extKey) {
        $tsSetup = $this->frontendConfigurationManager->getTypoScriptSetup();
        return $tsSetup['plugin.'][$extKey . '.'] ?: null;
    }

    protected function buildSettingsData($tsSettings) {
        $base = [];
        $instances = [];
        foreach ($tsSettings as $key => $value) {
            if (preg_match('/^[0-9]+\.$/', $key)) {
                $instances[] = $value;
            } else {
                $base[$key] = $value;
            }
        }
        $settings = [];
        if (count($instances)) {
            foreach ($instances as $instance) {
                $mergeResult = [];
                ArrayUtility::mergeRecursiveWithOverrule($mergeResult, $base);
                ArrayUtility::mergeRecursiveWithOverrule($mergeResult, $instance);
                $settings[] = $mergeResult;
            }
        } else {
            $settings[] = $base;
        }
        return $settings;
    }

    protected function buildSettings($extKey) {
        if (!isset($this->extSettingsRaw[$extKey])) {
            $this->extSettingsRaw[$extKey] = $this->getExtensionTypoScriptSetup($extKey);
        }
        $mergedSettingsRaw = [];
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->extSettingsRaw[$extKey]);
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->overwriteSettingsRaw[$extKey] ?: []);
        $settings = $this->buildSettingsData($mergedSettingsRaw);
        $this->settings[$extKey] = $settings;
        return $settings;
    }

    public function getSettingsCount($extKey) {
        return count($this->getSettings($extKey));
    }

    public function getSettings($extKey, $index = -1) {
        if ($index >= 0) {
            return $this->getSettings($extKey)[$index];
        }
        if (!isset($this->settings[$extKey])) {
            $this->settings[$extKey] = $this->buildSettings($extKey);
        }
        return $this->settings[$extKey];
    }
}
