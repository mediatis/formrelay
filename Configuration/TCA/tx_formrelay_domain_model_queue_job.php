<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:formrelay/Resources/Private/Language/locallang_db.xml:';
$readOnly = false;

$GLOBALS['TCA']['tx_formrelay_domain_model_queue_job'] = [
    'ctrl' => [
        'label' => 'created',
        'tstamp' => 'changed',
        'crdate' => 'created',
        'title' => $ll . 'tx_formrelay_domain_model_queue_job',
        'origUid' => 't3_origuid',
        'searchFields' => 'created,status,status_message,changed,serialized_data',
        'iconfile' => 'EXT:formrelay/Resources/Public/Icons/QueueJob.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'created,changed,status,status_message,serialized_data',
    ],
    'types' => [
        '0' => [
            'showitem' => 'created,changed,status,status_message,serialized_data'
        ],
    ],
    'palettes' => [
        '0' => ['showitem' => 'created,changed,status,status_message,serialized_data'],
    ],
    'columns' => [
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
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'readOnly' => $readOnly,
            ],
        ],
    ],
];
