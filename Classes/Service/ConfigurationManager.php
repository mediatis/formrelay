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
     * Functionality:
     *
     * formrelay settings consist of one set of base settings and a list of instance settings, which can overwrite the base settings
     *
     * tx_formrelay_xyz {
     *     key_1 = value_1
     *     key_2 = value_2
     *     key_3 = value_3
     *     1 {
     *         key_2 = value_2_b
     *     }
     *     2 {
     *         key_1 = value_1_b
     *         key_2 = value_2_c
     *     }
     * }
     *
     * forms/form submissions can provide an overwrite for these settings, their format is identical
     *
     * tx_formrelay_xyz {
     *     key_3 = value_3_b
     *     2 {
     *         key_2 = value_2_d
     *     }
     * }
     *
     * the configuration manager implements the overwrite from the form submissions
     * and then creates the instance settings by merging their base settings with the instance settings
     *
     * tx_formrelay_xyz {
     *     0 {
     *         key_1 = value_1
     *         key_2 = value_2_b
     *         key_3 = value_3_b
     *     }
     *     1 {
     *         key_1 = value_1_b
     *         key_2 = value_2_d
     *         key_3 = value_3_b
     *     }
     * }
     */


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

    public function setFormrelaySettingsOverwrite($overwriteSettings)
    {
        $this->overwriteSettingsRaw = $this->typoScriptService->convertPlainArrayToTypoScriptArray($overwriteSettings);
        $this->settings = [];
    }

    public function getFormrelaySettingsOverwrite()
    {
        return $this->overwriteSettingsRaw;
    }

    public function getExtensionTypoScriptSetup($extKey) {
        $tsSetup = $this->frontendConfigurationManager->getTypoScriptSetup();
        return $tsSetup['plugin.'][$extKey . '.'] ?: null;
    }

    protected function buildFormrelaySettingsData($tsSettings) {
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

    protected function buildFormrelaySettings($extKey) {
        if (!isset($this->extSettingsRaw[$extKey])) {
            $this->extSettingsRaw[$extKey] = $this->getExtensionTypoScriptSetup($extKey);
        }
        $mergedSettingsRaw = [];
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->extSettingsRaw[$extKey]);
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->overwriteSettingsRaw[$extKey] ?: []);
        $settings = $this->buildFormrelaySettingsData($mergedSettingsRaw);
        $this->settings[$extKey] = $settings;
        return $settings;
    }

    public function getFormrelaySettingsCount($extKey) {
        return count($this->getFormrelaySettings($extKey));
    }

    public function getFormrelaySettings($extKey, $index = -1) {
        if ($index >= 0) {
            return $this->getFormrelaySettings($extKey)[$index];
        }
        if (!isset($this->settings[$extKey])) {
            $this->settings[$extKey] = $this->buildFormrelaySettings($extKey);
        }
        return $this->settings[$extKey];
    }
}
