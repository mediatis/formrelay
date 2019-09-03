<?php

namespace Mediatis\Formrelay\Configuration;

use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use Mediatis\Formrelay\Utility\ArrayUtility;

class ConfigurationManager implements SingletonInterface
{
    const SIGNAL_UPDATE_CONFIG = 'updateConfig';

    const KEY_BASE_SETTINGS = 'ext';

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

    // base settings for all plugins stored in formrelay.settings.ext
    protected $formrelayExtSettingsRaw = [];
    protected $overwriteFormrelayExtSettingsRaw = [];

    // array of settings specific for each extension
    protected $extSettingsRaw = [];
    protected $overwriteSettingsRaw = [];

    // buffered result for each extension, including defaults, overwrites and process cascading (as described above)
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

    protected function updateConfig(array $settings, array $context = []): array
    {
        $settings = $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            static::SIGNAL_UPDATE_CONFIG,
            [$settings, $context]
        )[0];
        return $settings;
    }

    public function setFormrelaySettingsOverwrite(array $overwriteSettings)
    {
        $context = ['environment' => 'backend'];
        foreach ($overwriteSettings as $extKey => $settings) {
            $context['extKey'] = $extKey;
            $overwriteSettings[$extKey] = $this->updateConfig($settings, $context)['settings'] ?: [];
        }
        $this->overwriteSettingsRaw = $overwriteSettings;
        $this->overwriteFormrelayExtSettingsRaw = $this->overwriteSettingsRaw['formrelay']['settings'][static::KEY_BASE_SETTINGS] ?: [];
        $this->settings = [];
    }

    public function getExtensionTypoScriptSetup(string $extKey): array
    {
        $tsSetup = $this->frontendConfigurationManager->getTypoScriptSetup();
        $tsExtensionSetup = $tsSetup['plugin.'][$extKey . '.'] ?: [];
        if (!empty($tsExtensionSetup)) {
            $tsExtensionSetup = $this->typoScriptService->convertTypoScriptArrayToPlainArray($tsExtensionSetup);
        }
        return $tsExtensionSetup;
    }

    protected function buildFormrelaySettingsCascade($tsSettings): array
    {
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
                ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergeResult, $base);
                ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergeResult, $instance);
                $settings[] = $mergeResult;
            }
        } else {
            $mergeResult = [];
            ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergeResult, $base);
            $settings[] = $mergeResult;
        }
        return $settings;
    }

    protected function buildFormrelaySettings($extKey)
    {
        if ($this->formrelayExtSettingsRaw === null) {
            $this->formrelayExtSettingsRaw = $this->getExtensionTypoScriptSetup('tx_formrelay')['settings'][static::KEY_BASE_SETTINGS] ?: [];
        }
        if (!isset($this->extSettingsRaw[$extKey])) {
            $this->extSettingsRaw[$extKey] = $this->updateConfig(
                $this->getExtensionTypoScriptSetup($extKey),
                ['environment' => 'typoscript'],
                $extKey
            )['settings'];
        }

        $mergedSettingsRaw = [];
        // we disable unsetFeature because it should happen in the last merge in buildFormrelaySettingsCascade @TODO write a test case for this
        ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergedSettingsRaw, $this->formrelayExtSettingsRaw, true, true, false);
        ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergedSettingsRaw, $this->overwriteFormrelayExtSettingsRaw, true, true, false);
        ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergedSettingsRaw, $this->extSettingsRaw[$extKey], true, true, false);
        ArrayUtility::plainArrayMergeRecursiveWithOverrule($mergedSettingsRaw, $this->overwriteSettingsRaw[$extKey] ?: [], true, true, false);
        $settings = $this->buildFormrelaySettingsCascade($mergedSettingsRaw);
        $this->settings[$extKey] = $settings;
    }

    public function getFormrelaySettingsCount($extKey)
    {
        return count($this->getFormrelaySettings($extKey));
    }

    public function getFormrelaySettings($extKey, $index = -1)
    {
        if ($index >= 0) {
            return $this->getFormrelaySettings($extKey)[$index];
        }
        if (!isset($this->settings[$extKey])) {
            $this->buildFormrelaySettings($extKey);
        }
        return $this->settings[$extKey];
    }
}
