<?php

namespace Mediatis\Formrelay\Configuration;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class ConfigurationManager implements SingletonInterface
{
    const SIGNAL_UPDATE_CONFIG = 'updateConfig';

    /**
     * Functionality:
     *
     * formrelay extension settings consist of one set of base settings and a list of instance settings, which can overwrite the base settings
     *
     * plugin.tx_formrelay_xyz.settings {
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
     * the formrelay settings themselves can provide basic settings which are used as defaults if plugin.tx_formrelay_xyz does not overwrite them
     *
     * plugin.tx_formrelay.settings {
     *     # other settings...
     *     ext {
     *         key_3 = value_3_b
     *         key_4 = value_4
     *         key_5 = value_5
     *         1 {
     *             key_5 = value_5_c
     *         }
     *     }
     * }
     *
     * forms/form submissions can provide an overwrite for all these settings, their format is identical
     *
     * tx_formrelay.settings.ext {
     *     key_5 = value_5_b
     * }
     *
     * tx_formrelay_xyz.settings {
     *     key_3 = value_3_c
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
     *         key_3 = value_3_c
     *         key_4 = value_4
     *         key_5 = value_5_b
     *     }
     *     1 {
     *         key_1 = value_1_b
     *         key_2 = value_2_d
     *         key_3 = value_3_c
     *         key_4 = value_4
     *         key_5 = value_5_c
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

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    protected $formrelayExtSettingsRaw = null;
    protected $extSettingsRaw = [];
    protected $overwriteSettingsRaw = [];
    protected $settings = [];

    public function injectTypoScriptService(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectFrontendConfigurationManager(FrontendConfigurationManager $frontendConfigurationManager)
    {
        $this->frontendConfigurationManager = $frontendConfigurationManager;
    }

    public function setFormrelaySettingsOverwrite(array $overwriteSettings)
    {
        $overwriteSettings = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            static::SIGNAL_UPDATE_CONFIG,
            [$overwriteSettings, ['environment' => 'backend']]
        )[0];
        $this->overwriteSettingsRaw = $overwriteSettings['settings'];
        $this->settings = [];
    }

    public function getFormrelaySettingsOverwrite()
    {
        return $this->overwriteSettingsRaw;
    }

    public function getExtensionTypoScriptSetup(string $extKey) {
        $tsSetup = $this->frontendConfigurationManager->getTypoScriptSetup();
        $tsExtensionSetup = $tsSetup['plugin.'][$extKey . '.'] ?: [];
        $tsExtensionSetup = $this->typoScriptService->convertTypoScriptArrayToPlainArray($tsExtensionSetup);
        $tsExtensionSetup = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            static::SIGNAL_UPDATE_CONFIG,
            [$tsExtensionSetup, ['environment' => 'typoscript']]
        )[0];
        return $tsExtensionSetup;
    }

    protected function buildFormrelaySettingsCascade($tsSettings) {
        $base = [];
        $instances = [];
        foreach ($tsSettings as $key => $value) {
            if (is_numeric($key)) {
                $instances[] = $value;
            } else {
                $base[$key] = $value;
            }
        }
        $settings = [];
        if (count($instances) > 0) {
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
        if ($this->formrelayExtSettingsRaw === null) {
            $this->formrelayExtSettingsRaw = $this->getExtensionTypoScriptSetup('tx_formrelay')['settings']['ext'] ?: [];
        }
        if (!isset($this->extSettingsRaw[$extKey])) {
            $this->extSettingsRaw[$extKey] = $this->getExtensionTypoScriptSetup($extKey)['settings'];
        }
        $mergedSettingsRaw = [];
        // we disable unsetFeature because it should happen in the last merge in buildFormrelaySettingsData @TODO write a test case for this
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->formrelayExtSettingsRaw, true, true, false);
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->extSettingsRaw[$extKey], true, true, false);
        ArrayUtility::mergeRecursiveWithOverrule($mergedSettingsRaw, $this->overwriteSettingsRaw[$extKey] ?: [], true, true, false);
        $settings = $this->buildFormrelaySettingsCascade($mergedSettingsRaw);
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
