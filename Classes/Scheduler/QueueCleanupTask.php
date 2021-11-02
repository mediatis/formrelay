<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueInterface;

class QueueCleanupTask extends QueueTask
{
    const MIN_AGE = 3600 * 24 * 30; // 30 days

    /** @var QueueInterface */
    protected $queue;

    protected $minAge = self::MIN_AGE;
    protected $doneOnly = false;

    protected function prepareTask()
    {
        parent::prepareTask();
        $this->queue = $this->registry->getQueue();
    }

    public function getMinAge(): int
    {
        return $this->minAge;
    }

    public function setMinAge(int $minAge)
    {
        $this->minAge = $minAge;
    }

    public function getDoneOnly(): bool
    {
        return $this->doneOnly;
    }

    public function setDoneOnly(bool $doneOnly)
    {
        $this->doneOnly = $doneOnly;
    }

    public function execute()
    {
        $this->prepareTask();
        $this->queue->removeOldJobs(
            $this->minAge,
            $this->doneOnly ? [QueueInterface::STATUS_DONE] : []
        );
        return true;
    }
}
