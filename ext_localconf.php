<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

// Register Hook to preprocess Formmail Mail forms
// $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['sendFormmail-PreProcClass'][] = 'EXT:leica_sendform/hook/class.tx_leicasendform_sendform.php:tx_leicasendform_sendform';



// Form Wizard Hook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['form']['hooks']['renderWizard'][] = 'Mediatis\\Formrelay\\Hooks\\FormWizardViewHook->initialize';



// Add data providers
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'EXT:formrelay/Classes/DataProvider/Adwords';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'EXT:formrelay/Classes/DataProvider/AdwordCampains';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'][] = 'EXT:formrelay/Classes/DataProvider/LanguageCode';

?>