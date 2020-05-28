<?php

namespace Mediatis\Formrelay\Extensions\Form;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Mediatis\Formrelay\Service\Relay;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;


class FormFinisher extends AbstractFinisher
{
    const SIGNAL_PROCESS_FORM_ELEMENT = 'processFormElement';

    /** @var Logger */
    protected $logger;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var Relay */
    protected $relay;

    /** @var array */
    protected $defaultOptions = [
        'setup' => '',
        'baseUploadPath' => 'uploads/tx_formrelay/',
    ];

    protected $formValueMap = [];

    public function initializeObject()
    {
        $logManager = $this->objectManager->get(LogManager::class);
        $this->logger = $logManager->getLogger(static::class);
    }

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectRelay(Relay $relay)
    {
        $this->relay = $relay;
    }

    protected function executeInternal()
    {
        $setup = trim($this->parseOption('setup'));

        if ($setup) {
            $typoScriptParser = $this->objectManager->get(TypoScriptParser::class);
            $typoScriptService = $this->objectManager->get(TypoScriptService::class);
            $typoScriptParser->parse($setup);
            $typoScript = $typoScriptParser->setup;
            $formSettings = $typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);
        } else {
            $formSettings = [];
        }

        $this->formValueMap = $this->finisherContext->getFormValues();
        $formRuntime = $this->finisherContext->getFormRuntime();

        $options = [
            'baseUploadPath' => $this->parseOption('baseUploadPath'),
        ];

        $formValues = [];
        $elements = $formRuntime->getFormDefinition()->getRenderablesRecursively();
        /** @var AbstractFormElement $element */
        foreach ($elements as $element) {
            $type = $element->getType();
            $id = $element->getIdentifier();
            $value = isset($this->formValueMap[$id]) ? $this->formValueMap[$id] : null;

            $processed = false;
            // default element processors are within
            // the namespace \Mediatis\Formrelay\Extensions\Form\ElementProcessor
            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                static::SIGNAL_PROCESS_FORM_ELEMENT,
                [$element, $value, $options, &$formValues, &$processed]
            );
            if (!$processed) {
                $this->logger->error('Ignoring unknown form field type.', [
                    'form' => $element->getRootForm()->getIdentifier(),
                    'field' => $id,
                    'class' => get_class($element),
                    'type' => $type,
                ]);
            }
        }

        $this->relay->process($formValues, $formSettings, false);
    }
}
