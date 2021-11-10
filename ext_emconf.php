<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Relay',
    'description' => 'Send form data to different providers',
    'category' => 'be',
    'author' => 'Michael Vöhringer',
    'author_email' => 'voehringer@mediatis.de',
    'author_company' => 'Mediatis AG',
    'state' => 'stable',
    'version' => '6.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'form_fieldnames' => '>=3.3.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
