<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:formrelay/Resources/Private/Language/locallang_db.xml:';
$readOnly = false;

$GLOBALS['TCA']['tx_formrelay_domain_model_queue_job'] = [
    'ctrl' => [
        'label' => 'created',
        'label_alt' => 'label',
        'label_alt_force' => 1,
        'tstamp' => 'changed',
        'crdate' => 'created',
        'title' => $ll . 'tx_formrelay_domain_model_queue_job',
        'origUid' => 't3_origuid',
        'searchFields' => 'label,hash,route,pass,created,status,skipped,status_message,changed',
        'iconfile' => 'EXT:formrelay/Resources/Public/Icons/QueueJob.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'label,hash,route,pass,created,changed,status,skipped,status_message,serialized_data',
    ],
    'types' => [
        '0' => [
            'showitem' => 'label,hash,route,pass,created,changed,status,skipped,status_message,serialized_data'
        ],
    ],
    'palettes' => [
        '0' => ['showitem' => 'label,hash,route,pass,created,changed,status,skipped,status_message,serialized_data'],
    ],
    'columns' => [
        'label' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.label',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'skipped' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.skipped',
            'config' => [
                'type' => 'check',
                'readOnly' => $readOnly,
            ],
        ],
        'hash' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.hash',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'route' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.route',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'pass' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.pass',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'created' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.created',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => $readOnly,
            ],
        ],
        'changed' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.changed',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => $readOnly,
            ],
        ],
        'status' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Pending', \FormRelay\Core\Queue\QueueInterface::STATUS_PENDING],
                    ['Running', \FormRelay\Core\Queue\QueueInterface::STATUS_RUNNING],
                    ['Done', \FormRelay\Core\Queue\QueueInterface::STATUS_DONE],
                    ['Failed', \FormRelay\Core\Queue\QueueInterface::STATUS_FAILED],
                ],
                'readOnly' => $readOnly,
            ],
        ],
        'status_message' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.status_message',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'readOnly' => $readOnly,
            ],
        ],
        'serialized_data' => [
            'exclude' => 1,
            'label' => $ll . 'tx_formrelay_domain_model_queue_job.serialized_data',
            'config' => [
                'type' => 'user',
                'renderType' => 'formrelayJsonFieldElement',
                'parameters' => [
                    'cols' => 40,
                    'rows' => 15,
                    'readOnly' => $readOnly,
                ],
            ],
        ],
    ],
];
