<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () {
    $typoscript = '# frontend configuration
plugin.tx_form.settings.yamlConfigurations {
  1590510649 = EXT:formrelay/Configuration/Yaml/BaseSetup.yaml
  1590510650 = EXT:formrelay/Configuration/Yaml/FormEngineSetup.yaml
}

# backend configuration
module.tx_form.settings.yamlConfigurations {
  1590510649 = EXT:formrelay/Configuration/Yaml/BaseSetup.yaml
  1590510650 = EXT:formrelay/Configuration/Yaml/FormEditorSetup.yaml
  1590510651 = EXT:formrelay/Configuration/Yaml/FormEngineSetup.yaml
}';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($typoscript);
})();

(function () {
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $dispatcher */
    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

    // relay initalization
    $dispatcher->connect(
        \FormRelay\Core\Service\RegistryInterface::class,
        \Mediatis\Formrelay\Factory\RegistryFactory::SIGNAL_UPDATE_REGISTRY,
        \Mediatis\Formrelay\Initialization::class,
        'initialize'
    );

    // add form element processors (ext:form)
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('form')) {
        $elementsProcessorClasses = [
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\AbstractSectionElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\ContentElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\DateElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\DatePickerElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\FileUploadElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\GenericElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\HoneypotElementProcessor::class,
            \Mediatis\Formrelay\Extensions\Form\ElementProcessor\StaticTextElementProcessor::class,
        ];
        foreach ($elementsProcessorClasses as $elementsProcessorClass) {
            $dispatcher->connect(
                \Mediatis\Formrelay\Extensions\Form\FormDataProcessor::class,
                \Mediatis\Formrelay\Extensions\Form\FormDataProcessor::SIGNAL_PROCESS_FORM_ELEMENT,
                $elementsProcessorClass,
                \Mediatis\Formrelay\Extensions\Form\FormDataProcessor::SIGNAL_PROCESS_FORM_ELEMENT
            );
        }
    }
})();
