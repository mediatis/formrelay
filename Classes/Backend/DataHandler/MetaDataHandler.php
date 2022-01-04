<?php

namespace Mediatis\Formrelay\Backend\DataHandler;

use Mediatis\Formrelay\Domain\Model\Queue\Job;
use Mediatis\Formrelay\Factory\QueueDataFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class MetaDataHandler implements SingletonInterface
{
    protected function updateJobData(&$fieldArray)
    {
        $queueDataFactory = new QueueDataFactory();
        $job = new Job();
        $serializedData = json_decode($fieldArray['serialized_data'] ?? '');
        if (!$serializedData) {
            $job->setSerializedData('');
        } else {
            $job->setSerializedData(json_encode($serializedData));
        }
        $job->setHash($fieldArray['hash'] ?? '');

        if ($queueDataFactory->updateLegacyJobData($job)) {
            $fieldArray['serialized_data'] = json_encode(json_decode($job->getSerializedData()));
        }

        $fieldArray['route'] = $queueDataFactory->getJobRoute($job);
        $fieldArray['pass'] = $queueDataFactory->getJobRoutePass($job);

        $job->setHash($queueDataFactory->getJobHash($job));
        $fieldArray['hash'] = $job->getHash();

        $job->setLabel($queueDataFactory->getJobLabel($job));
        $fieldArray['label'] = $job->getLabel();
    }

    public function processDatamap_preProcessFieldArray(&$fieldArray, $table, $id, DataHandler $parentObj)
    {
        if (($table === 'tx_formrelay_domain_model_queue_job') && !$parentObj->isImporting) {
            $this->updateJobData($fieldArray);
        }
    }
}
