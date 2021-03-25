<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueProcessor;
use FormRelay\Core\Service\QueueWorker;
use Mediatis\Formrelay\Domain\Repository\Queue\JobRepository;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class QueueProcessorTask extends AbstractTask
{
    const BATCH_SIZE = 10;

    public $pid = 0;
    public $batchSize = 10;

    /** @var QueueProcessor */
    protected $queueProcessor;

    protected function prepareTask()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var JobRepository $queue */
        $queue = $objectManager->get(JobRepository::class);
        $queue->setPid($this->pid);

        /** @var RegistryFactory $registryFactory */
        $registryFactory = $objectManager->get(RegistryFactory::class);

        $registry = $registryFactory->buildRegistry();

        /** @var QueueWorker $worker */
        $worker = GeneralUtility::makeInstance(QueueWorker::class, $registry);

        $this->queueProcessor = GeneralUtility::makeInstance(QueueProcessor::class, $queue, $worker);
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid)
    {
        $this->pid = $pid;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function setBatchSize(int $batchSize)
    {
        $this->batchSize = $batchSize;
    }

    public function execute()
    {
        $this->prepareTask();
        $this->queueProcessor->processBatch($this->batchSize);
        return true;
    }
}
