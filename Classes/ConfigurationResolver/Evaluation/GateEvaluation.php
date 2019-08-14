<?php

namespace Mediatis\Formrelay\ConfigurationResolver\Evaluation;

use Mediatis\Formrelay\Configuration\ConfigurationManager;

class GateEvaluation extends Evaluation
{

    /** @var ConfigurationManager */
    protected $configurationManager;

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    protected function evaluateMultipleExtensions($context, $keysEvaluated) {
        /*
         * # case 1: multiple extension keys, no indices
         *
         * gate = tx_formrelay_a,tx_formrelay_b
         * =>
         * or {
         *     1.gate {
         *         extKey = a
         *         index = any
         *     }
         *     2.gate {
         *         extKey = b
         *         index = any
         *     }
         * }
         */
        $extKeys = explode(',', $this->config);
        $gateConfig = ['or' => []];
        foreach ($extKeys as $extKey) {
            $gateConfig['or'][] = ['gate' => ['extKey' => $extKey, 'index' => 'any']];
        }
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, $gateConfig);
        return $evaluation->eval($context, $keysEvaluated);
    }

    protected function evaluateMultipleIndices($context, $keysEvaluated)
    {
        /*
         * # case 2: one extension key, indirect indices (any|all)
         *
         * gate { extKey=tx_formrelay_a, index=any|all }
         * =>
         * or|and {
         *     1.gate { extKey=tx_formrelay_a, index=0 }
         *     2.gate { extKey=tx_formrelay_a, index=1 }
         *     # ...
         *     n.gate { extKey=tx_formrelay_a, index=n }
         * }
         */
        $extKey = $this->config['extKey'];
        $gateConfigs = [];
        $count = $this->configurationManager->getFormrelaySettingsCount($extKey);
        for ($i = 0; $i < $count; $i++) {
            $gateConfigs[] = ['gate' => ['extKey' => $extKey, 'index' => $i]];
        }
        $evaluation = $this->objectManager->get(GeneralEvaluation::class, [$this->config['index'] === 'any' ? 'or' : 'and' => $gateConfigs]);
        return $evaluation->eval($context, $keysEvaluated);
    }

    public function eval(array $context = [], array $keysEvaluated = [])
    {
        if (!is_array($this->config)) {
            return $this->evaluateMultipleExtensions($context, $keysEvaluated);
        }

        $extKey = $this->config['extKey'];
        $index = $this->config['index'];
        if ($this->config['index'] === 'any' || $this->config['index'] === 'all') {
            return $this->evaluateMultipleIndices($context, $keysEvaluated);
        }

        /*
         * # case 3: one extension key, one index
         * gate { extKey=tx_formrelay_a, index=n }
         * =>
         * actual evaluation of extension gate
         */
        if (isset($keysEvaluated[$extKey]) && in_array($index, $keysEvaluated[$extKey])) {
            return false;
        }
        $keysEvaluated[$extKey][] = $index;
        $settings = $this->configurationManager->getFormrelaySettings($extKey, $index);
        if (!$settings['enabled']) {
            return false;
        }
        if (isset($settings['gate']) && !empty($settings['gate'])) {
            $evaluation = $this->objectManager->get(GeneralEvaluation::class, $settings['gate']);
            return $evaluation->eval($context, $keysEvaluated);
        }
        return true;
    }
}
