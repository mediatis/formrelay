<?php

namespace Mediatis\Formrelay\Scheduler;

use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

class QueueProcessorFieldProvider extends QueueFieldProvider
{
    /**
     * @param array $taskInfo
     * @param QueueProcessorTask|null $task
     * @param SchedulerModuleController $parentObject
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        $additionalFields = [];

        if ($task) {
            $taskInfo['pid'] = $task->getPid();
            $taskInfo['batchSize'] = $task->getBatchSize();
        } else {
            $taskInfo['pid'] = 0;
            $taskInfo['batchSize'] = QueueProcessorTask::BATCH_SIZE;
        }

        $this->addField($additionalFields, $taskInfo, 'pid', 'ID of the folder that contains the submission jobs.');
        $this->addField($additionalFields, $taskInfo, 'batchSize', 'Batch size of jobs to process per run');

        return $additionalFields;
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $submittedData['pid'] = (int)$submittedData['pid'];
        $submittedData['batchSize'] = (int)$submittedData['batchSize'];
        return true;
    }

    /**
     * @param array $submittedData
     * @param QueueProcessorTask|null $task
     */
    public function saveAdditionalFields(array $submittedData, $task)
    {
        $task->setPid($submittedData['pid']);
        $task->setBatchSize($submittedData['batchSize']);
    }
}
