<?php

namespace Mediatis\Formrelay\Scheduler;

use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

class QueueCleanupFieldProvider extends QueueFieldProvider
{
    /**
     * @param array $taskInfo
     * @param QueueCleanupTask|null $task
     * @param SchedulerModuleController $parentObject
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        $additionalFields = [];

        if ($task) {
            $taskInfo['pid'] = $task->getPid();
            $taskInfo['minAge'] = $task->getMinAge();
            $taskInfo['doneOnly'] = $task->getDoneOnly() ? 1 : 0;
        } else {
            $taskInfo['pid'] = 0;
            $taskInfo['minAge'] = QueueCleanupTask::MIN_AGE;
            $taskInfo['doneOnly'] = 0;
        }

        $this->addField($additionalFields, $taskInfo, 'pid', 'ID of the folder that contains the submission jobs.');
        $this->addField($additionalFields, $taskInfo, 'minAge', 'Minimum age in seconds for the jobs that are to be deleted');
        $this->addCheckboxField($additionalFields, $taskInfo, 'doneOnly', 'Delete only jobs with status "done"');

        return $additionalFields;
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $parentObject)
    {
        $submittedData['pid'] = (int)$submittedData['pid'];
        $submittedData['minAge'] = (int)$submittedData['minAge'];
        $submittedData['doneOnly'] = isset($submittedData['doneOnly']) ? !!$submittedData['doneOnly'] : false;
        return true;
    }

    /**
     * @param array $submittedData
     * @param QueueCleanupTask|null $task
     */
    public function saveAdditionalFields(array $submittedData, $task)
    {
        $task->setPid($submittedData['pid']);
        $task->setMinAge($submittedData['minAge']);
        $task->setDoneOnly($submittedData['doneOnly']);
    }
}
