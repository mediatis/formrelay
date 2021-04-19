<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueInterface;
use Mediatis\Formrelay\Domain\Repository\Queue\JobRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

abstract class QueueTask extends AbstractTask
{
    protected $pid = 0;

    /** @var QueueInterface */
    protected $queue;

    protected function prepareTask()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var JobRepository $queue */
        $this->queue = $objectManager->get(JobRepository::class);
        $this->queue->setPid($this->pid);
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid)
    {
        $this->pid = $pid;
    }
}
