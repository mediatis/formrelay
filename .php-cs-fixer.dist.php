<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('node_modules')
    ->exclude('vendor');

return \TYPO3\CodingStandards\CsFixerConfig::create()->setFinder($finder);
