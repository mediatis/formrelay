<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Relay',
    'description' => 'Send form data to different providers',
    'category' => 'be',
    'author' => 'Michael Vöhringer',
    'author_email' => 'voehringer@mediatis.de',
    'author_company' => 'Mediatis AG',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '1',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '4.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'form_fieldnames' => '2.0.0-3.0.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
