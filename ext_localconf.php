<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add data providers
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\Adwords::class);
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\AdwordCampains::class);
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\LanguageCode::class);
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\IpAddress::class);
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\Timestamp::class);
\Mediatis\Formrelay\Utility\FormrelayUtility::registerDataProvider(\Mediatis\Formrelay\DataProvider\ContentElement::class);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \Mediatis\Formrelay\Command\FormSimulationCommandController::class;
