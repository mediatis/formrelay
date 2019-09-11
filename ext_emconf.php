<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Relay',
    'description' => 'Send form data to different providers',
    'category' => 'be',
    'author' => 'Michael Vöhringer',
    'author_email' => 'voehringer@mediatis.de',
    'author_company' => 'Mediatis AG',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => '1',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.5.0-9.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
