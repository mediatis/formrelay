<?php

namespace Mediatis\Formrelay\Extensions\Form;

use FormRelay\Core\Service\Relay;
use Mediatis\Formrelay\Factory\RegistryFactory;
use Mediatis\Formrelay\Factory\SubmissionFactory;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;


class FormFinisher extends AbstractFinisher
{
    /** @var RegistryFactory */
    protected $registryFactory;

    /** @var SubmissionFactory */
    protected $submissionFactory;

    /** @var FormDataProcessor */
    protected $formDataProcessor;

    /** @var array */
    protected $defaultOptions = [
        'setup' => '',
        'baseUploadPath' => 'uploads/tx_formrelay/',
    ];

    public function injectRegistryFactory(RegistryFactory $registryFactory)
    {
        $this->registryFactory = $registryFactory;
    }

    public function injectSubmissionFactory(SubmissionFactory $submissionFactory)
    {
        $this->submissionFactory = $submissionFactory;
    }

    public function injectFormDataProcessor(FormDataProcessor $formDataProcessor)
    {
        $this->formDataProcessor = $formDataProcessor;
    }

    protected function buildFormValues()
    {
        $elements = $this->finisherContext
            ->getFormRuntime()
            ->getFormDefinition()
            ->getRenderablesRecursively();
        $elementValues = $this->finisherContext->getFormValues();
        $options = [
            'baseUploadPath' => $this->parseOption('baseUploadPath'),
        ];
        return $this->formDataProcessor->process($elements, $elementValues, $options);
    }

    protected function buildFormSettings()
    {
        $setup = trim($this->parseOption('setup'));
        if ($setup) {
            $typoScriptParser = $this->objectManager->get(TypoScriptParser::class);
            $typoScriptParser->parse($setup);
            $formSettings = $typoScriptParser->setup;
        } else {
            $formSettings = [];
        }
        return $formSettings;
    }

    protected function executeInternal()
    {
        $formValues = $this->buildFormValues();
        $formSettings = $this->buildFormSettings();

        $registry = $this->registryFactory->buildRegistry();
        $submission = $this->submissionFactory->buildSubmission($registry, $formValues, $formSettings);

        $relay = new Relay($registry);
        $relay->process($submission);
    }
}
