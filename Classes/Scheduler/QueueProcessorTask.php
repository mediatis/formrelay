<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueProcessor;
use FormRelay\Core\Service\QueueWorker;
use FormRelay\Core\Service\Relay;
use FormRelay\Core\Service\RelayInterface;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class QueueProcessorTask extends QueueTask
{
    const BATCH_SIZE = 10;

    public $batchSize = self::BATCH_SIZE;

    /** @var RelayInterface */
    protected $relay;

    protected function prepareTask()
    {
        parent::prepareTask();
        $this->relay = GeneralUtility::makeInstance(Relay::class, $this->registry);
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
        $this->relay->processFromQueue($this->batchSize);
        return true;
    }
}
