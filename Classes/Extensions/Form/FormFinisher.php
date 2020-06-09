<?php

namespace Mediatis\Formrelay\Extensions\Form;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Mediatis\Formrelay\Service\Relay;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;


class FormFinisher extends AbstractFinisher
{
    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var FormDataProcessor */
    protected $formDataProcessor;

    /** @var Relay */
    protected $relay;

    /** @var array */
    protected $defaultOptions = [
        'setup' => '',
        'baseUploadPath' => 'uploads/tx_formrelay/',
    ];

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectFormDataProcessor(FormDataProcessor $formDataProcessor)
    {
        $this->formDataProcessor = $formDataProcessor;
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
            $typoScriptParser->parse($setup);
            $formSettings = $typoScriptParser->setup;
        } else {
            $formSettings = [];
        }

        $elements = $this->finisherContext
            ->getFormRuntime()
            ->getFormDefinition()
            ->getRenderablesRecursively();
        $elementValues = $this->finisherContext->getFormValues();
        $options = [
            'baseUploadPath' => $this->parseOption('baseUploadPath'),
        ];

        $formValues = $this->formDataProcessor->process($elements, $elementValues, $options);

        $this->relay->process($formValues, $formSettings, false);
    }
}
