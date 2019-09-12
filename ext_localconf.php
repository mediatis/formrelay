<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () {
    $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    $registry = $objectManager->get(\Mediatis\Formrelay\Service\Registry::class);

    // add data providers
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\AdwordCampains::class);
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\Adwords::class);
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\ContentElement::class);
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\IpAddress::class);
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\LanguageCode::class);
    $registry->registerDataProvider(\Mediatis\Formrelay\DataProvider\Timestamp::class);

    // add evaluation types
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\AndEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\EmptyEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\EqualsEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\GateEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\GeneralEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\InEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\NotEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\OrEvaluation::class);
    $registry->registerEvaluation(\Mediatis\Formrelay\ConfigurationResolver\Evaluation\RequiredEvaluation::class);

    // add field mappers
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\AppendKeyValueFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\AppendValueFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\DiscreteFieldFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\DistributeFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\GeneralFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\IfEmptyFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\IfFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\IgnoreFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\JoinFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\NegateFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\PassthroughFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\PlainFieldMapper::class);
    $registry->registerFieldMapper(\Mediatis\Formrelay\ConfigurationResolver\FieldMapper\SplitFieldMapper::class);

    // add value mappers
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\GeneralValueMapper::class);
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\IfValueMapper::class);
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\NegateValueMapper::class);
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\PlainValueMapper::class);
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\RawValueMapper::class);
    $registry->registerValueMapper(\Mediatis\Formrelay\ConfigurationResolver\ValueMapper\SwitchValueMapper::class);

    // add content resolvers
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\FieldContentResolver::class);
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\GeneralContentResolver::class);
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\IfContentResolver::class);
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\InsertDataContentResolver::class);
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\PlainContentResolver::class);
    $registry->registerContentResolver(\Mediatis\Formrelay\ConfigurationResolver\ContentResolver\TrimContentResolver::class);

})();
