<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Form Wizard Hook
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['form']['hooks']['renderWizard'][] = 'Mediatis\\Formrelay\\Hooks\\FormWizardViewHook->initialize';

// Add data providers
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'Mediatis\\Formrelay\\DataProvider\\Adwords';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'Mediatis\\Formrelay\\DataProvider\\AdwordCampains';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'Mediatis\\Formrelay\\DataProvider\\LanguageCode';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'Mediatis\\Formrelay\\DataProvider\\IpAddress';


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \Mediatis\Formrelay\Command\FormSimulationCommandController::class;
