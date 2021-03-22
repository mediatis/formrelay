<?php

namespace Mediatis\Formrelay\Backend\DataHandler;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class MetaDataHandler implements SingletonInterface
{
    public function processDatamap_preProcessFieldArray(&$fieldArray, $table, $id, DataHandler $parentObj) {
        if (($table === 'tx_formrelay_domain_model_queue_job') && !$parentObj->isImporting) {
            $data = [];
            $serializedData = $fieldArray['serialized_data'];
            if ($serializedData) {
                $data = json_decode($serializedData, true);
            }
            $fieldArray['route'] = $data['context']['job']['route'] ?? '';
            $fieldArray['pass'] = $data['context']['job']['pass'] ?? '';
            $fieldArray['label'] = $fieldArray['route'] . '(' . $fieldArray['pass'] . ')';
        }
    }
}
