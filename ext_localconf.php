<?php

use Mediatis\Formrelay\Utility\FormrelayUtility;
use Mediatis\Formrelay\DataProvider;
use Mediatis\Formrelay\Command\FormSimulationCommandController;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add data providers
FormrelayUtility::registerDataProvider(DataProvider\Adwords::class);
FormrelayUtility::registerDataProvider(DataProvider\AdwordCampains::class);
FormrelayUtility::registerDataProvider(DataProvider\LanguageCode::class);
FormrelayUtility::registerDataProvider(DataProvider\IpAddress::class);
FormrelayUtility::registerDataProvider(DataProvider\Timestamp::class);
FormrelayUtility::registerDataProvider(DataProvider\ContentElement::class);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = FormSimulationCommandController::class;
