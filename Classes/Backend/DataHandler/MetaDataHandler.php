<?php

namespace Mediatis\Formrelay\Backend\DataHandler;

use FormRelay\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class MetaDataHandler implements SingletonInterface
{
    public function processDatamap_preProcessFieldArray(&$fieldArray, $table, $id, DataHandler $parentObj)
    {
        if (($table === 'tx_formrelay_domain_model_queue_job') && !$parentObj->isImporting) {
            $data = [];
            $serializedData = $fieldArray['serialized_data'];
            if ($serializedData) {
                $data = json_decode($serializedData, true);
            }
            $fieldArray['route'] = $data['context']['job']['route'] ?? 'undefined';
            $fieldArray['pass'] = $data['context']['job']['pass'] ?? 'undefined';

            if (isset($data['context']['submission']['hash'])) {
                $fieldArray['hash'] = $data['context']['submission']['hash'];
            } elseif (!empty($data)) {
                // if no hash is set, calculate it
                $fieldArray['hash'] = GeneralUtility::calculateHash($data);
            } else {
                // data is probably empty
                $fieldArray['hash'] = 'undefined';
            }

            if (isset($data['context']['job']['label'])) {
                $fieldArray['label'] = $data['context']['job']['label'];
            } elseif (isset($data['context']['job'])) {
                // legacy label for older records
                $fieldArray['label'] = GeneralUtility::calculateHash($data, true)
                    . '#' . $fieldArray['route']
                    . '#' . (is_numeric($fieldArray['pass']) ? $fieldArray['pass'] + 1 : $fieldArray['pass']);
            } else {
                // data is probably empty
                $fieldArray['label'] = 'undefined';
            }
        }
    }
}
