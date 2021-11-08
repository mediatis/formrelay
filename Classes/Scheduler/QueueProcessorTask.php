<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueProcessor;
use FormRelay\Core\Service\Relay;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QueueProcessorTask extends QueueTask
{
    const BATCH_SIZE = 10;

    public $batchSize = self::BATCH_SIZE;

    /** @var QueueProcessor */
    protected $queueProcessor;

    protected function prepareTask()
    {
        parent::prepareTask();

        /** @var Relay $relay */
        $relay = GeneralUtility::makeInstance(Relay::class, $this->registry);

        $this->queueProcessor = new QueueProcessor($this->queue, $relay);
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
