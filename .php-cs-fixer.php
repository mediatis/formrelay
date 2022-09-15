<?php
// https://github.com/TYPO3/coding-standards

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()
    ->exclude('templates')
    ->in(__DIR__)
;

return $config;
