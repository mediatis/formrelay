<?php

namespace Mediatis\Formrelay\Configuration;

use Mediatis\Formrelay\Utility\ArrayUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class ConfigurationManager implements SingletonInterface
{
    const SIGNAL_UPDATE_CONFIG = 'updateConfig';

    const KEY_BASE_SETTINGS = 'ext';

    /**
     * Functionality:
     *
     * formrelay extension settings consist of one set of base settings and a list of instance settings,
     * which can overwrite the base settings
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
     * the formrelay settings themselves can provide basic settings which are used as defaults
     * if plugin.tx_formrelay_xyz does not overwrite them
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

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    // array of settings specific for each extension
    protected $setup = [];
    protected $setupOverwrite = [];

    // buffered results for updated configurations (signal slot)
    protected $setupUpdated = [];
    protected $setupOverwriteUpdated = [];

    // buffered result for each extension, including default and overwrites, but not cascading
    protected $settings = [];

    // buffered result for each extension, including defaults, overwrites and process cascading (as described above)
    protected $cycleSettings = [];

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    protected function updateConfig(array $settings, array $context = []): array
    {
        return $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            static::SIGNAL_UPDATE_CONFIG,
            [$settings, $context]
        )[0];
    }

    protected function reset()
    {
        $this->setupUpdated = [];
        $this->setupOverwriteUpdated = [];
        $this->settings = [];
        $this->cycleSettings = [];
    }

    public function setSetup(array $setup)
    {
        $this->setup = $setup;
        $this->reset();
    }

    public function setSetupOverwrite(array $setupOverwrite)
    {
        $this->setupOverwrite = $setupOverwrite;
        $this->reset();
    }

    protected function fetchExtensionSetup(string $extKey): array
    {
        if (!isset($this->setupUpdated[$extKey])) {
            $context = ['environment' => 'typoscript', 'extKey' => $extKey];
            $extSetup = $this->setup[$extKey] ?? [];
            $this->setupUpdated[$extKey] = $this->updateConfig($extSetup, $context);
        }
        return $this->setupUpdated[$extKey]['settings'] ?? [];
    }

    protected function fetchExtensionSetupOverwrite(string $extKey): array
    {
        if (!isset($this->setupOverwriteUpdated[$extKey])) {
            $context = ['environment' => 'backend', 'extKey' => $extKey];
            $extSetup = $this->setupOverwrite[$extKey] ?: [];
            $this->setupOverwriteUpdated[$extKey] = $this->updateConfig($extSetup, $context);
        }
        return $this->setupOverwriteUpdated[$extKey]['settings'] ?? [];
    }

    protected function fetchBaseSetup(): array
    {
        return $this->fetchExtensionSetup('tx_formrelay')[static::KEY_BASE_SETTINGS] ?? [];
    }

    protected function fetchBaseSetupOverwrite(): array
    {
        return $this->fetchExtensionSetupOverwrite('tx_formrelay')[static::KEY_BASE_SETTINGS] ?? [];
    }

    protected function merge(bool $resolveUnsetFeature, ...$arrays)
    {
        $result = [];
        foreach ($arrays as $array) {
            ArrayUtility::plainArrayMergeRecursiveWithOverrule(
                $result,
                $array,
                true,
                true,
                $resolveUnsetFeature
            );
        }
        return $result;
    }

    protected function buildCycles($extSettings): array
    {
        $base = [];
        $cycles = [];
        foreach ($extSettings as $key => $value) {
            if (is_numeric($key)) {
                $cycles[] = $value;
            } else {
                $base[$key] = $value;
            }
        }
        $extCycleSettings = [];
        if (count($cycles) > 0) {
            foreach ($cycles as $cycle) {
                $extCycleSettings[] = $this->merge(true, $base, $cycle);
            }
        } else {
            $extCycleSettings[] = $this->merge(true, $base);
        }
        return $extCycleSettings;
    }

    public function getExtensionSettings(string $extKey, $resolveUnsetFeature = true): array
    {
        $extSettings = $this->settings[$extKey] ?? null;
        if (!$resolveUnsetFeature || !$extSettings) {
            $extSettings = $this->merge(
                $resolveUnsetFeature,
                $this->fetchBaseSetup(),
                $this->fetchBaseSetupOverwrite(),
                $this->fetchExtensionSetup($extKey),
                $this->fetchExtensionSetupOverwrite($extKey)
            );
            if ($resolveUnsetFeature) {
                $this->settings[$extKey] = $extSettings;
            }
        }
        return $extSettings;
    }

    public function getFormrelayCycles(string $extKey): array
    {
        if (!isset($this->cycleSettings[$extKey])) {
            $extSettings = $this->getExtensionSettings($extKey, false);
            $this->cycleSettings[$extKey] = $this->buildCycles($extSettings);
        }
        return $this->cycleSettings[$extKey];
    }

    public function getFormrelayCycle(string $extKey, int $index): array
    {
        $extCycleSettings = $this->getFormrelayCycles($extKey);
        return $extCycleSettings[$index];
    }

    public function getFormrelayCycleCount($extKey)
    {
        return count($this->getFormrelayCycles($extKey));
    }
}
