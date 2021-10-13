<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueProcessor;
use FormRelay\Core\Service\QueueWorker;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class QueueProcessorTask extends QueueTask
{
    const BATCH_SIZE = 10;

    public $batchSize = self::BATCH_SIZE;

    /** @var QueueProcessor */
    protected $queueProcessor;

    protected function prepareTask()
    {
        parent::prepareTask();

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var RegistryFactory $registryFactory */
        $registryFactory = $objectManager->get(RegistryFactory::class);

        $registry = $registryFactory->buildRegistry();

        /** @var QueueWorker $worker */
        $worker = GeneralUtility::makeInstance(QueueWorker::class, $registry);

        $this->queueProcessor = GeneralUtility::makeInstance(QueueProcessor::class, $this->queue, $worker);
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
