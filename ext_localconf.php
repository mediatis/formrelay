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
    // relay initalization
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerInitialization(\Mediatis\Formrelay\Initialization::class);

    // configuration updater
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerRouteConfigurationUpdater(\Mediatis\Formrelay\Configuration\ConfigurationUpdater::class);

    // add form element processors (ext:form)
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\AbstractSectionElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\ContentElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\DateElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\DatePickerElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\FileUploadElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\GenericElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\HoneypotElementProcessor::class);
    \Mediatis\Formrelay\Utility\RegistrationUtility::registerFormElementProcessor(\Mediatis\Formrelay\Extensions\Form\ElementProcessor\StaticTextElementProcessor::class);

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Mediatis\Formrelay\Scheduler\QueueProcessorTask::class] = [
        'extension' => 'formrelay',
        'title' => 'Formrelay Queue Worker',
        'description' => 'Processes the next batch of form submissions using the form-relay',
        'additionalFields' => \Mediatis\Formrelay\Scheduler\QueueProcessorFieldProvider::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Mediatis\Formrelay\Scheduler\QueueCleanupTask::class] = [
        'extension' => 'formrelay',
        'title' => 'Formrelay Queue Cleanup',
        'description' => 'Removes old submissions from the database to be compliant with data protection regulations',
        'additionalFields' => \Mediatis\Formrelay\Scheduler\QueueCleanupFieldProvider::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Mediatis\Formrelay\Backend\DataHandler\MetaDataHandler::class;

    // Add textarea with built-in json formatting
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1640091726] = [
        'nodeName' => 'formrelayJsonFieldElement',
        'priority' => 40,
        'class' => \Mediatis\Formrelay\Form\Element\JsonFieldElement::class,
    ];

})();
