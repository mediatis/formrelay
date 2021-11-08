<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Factory\QueueDataFactory as OriginalQueueDataFactory;
use FormRelay\Core\Model\Queue\JobInterface;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Queue\QueueInterface;
use Mediatis\Formrelay\Domain\Model\Queue\Job;

class QueueDataFactory extends OriginalQueueDataFactory
{
    const KEY_PID = 'pid';
    const DEFAULT_PID = 0;

    protected function createJob(): JobInterface
    {
        return new Job();
    }

    public function convertSubmissionToJob(SubmissionInterface $submission, string $route, int $pass, int $status = QueueInterface::STATUS_PENDING): JobInterface
    {
        /** @var Job $job */
        $job = parent::convertSubmissionToJob($submission, $route, $pass, $status);
        $job->setPid($submission->getConfiguration()->getWithRoutePassOverride(static::KEY_PID, $route, $pass, static::DEFAULT_PID));
        return $job;
    }
}
