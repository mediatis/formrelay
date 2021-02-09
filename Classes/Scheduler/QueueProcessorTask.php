<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Queue\QueueProcessor;
use FormRelay\Core\Service\QueueWorker;
use Mediatis\Formrelay\Domain\Model\Repository\Queue\JobRepository;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class QueueProcessorTask extends AbstractTask
{
    const BATCH_SIZE = 10;

    /** @var QueueProcessor */
    protected $queueProcessor;

    public function __construct()
    {
        parent::__construct();

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var JobRepository $queue */
        $queue = $objectManager->get(JobRepository::class);

        /** @var RegistryFactory $registryFactory */
        $registryFactory = $objectManager->get(RegistryFactory::class);

        $registry = $registryFactory->buildRegistry();

        /** @var QueueWorker $worker */
        $worker = GeneralUtility::makeInstance(QueueWorker::class, $registry);

        $this->queueProcessor = GeneralUtility::makeInstance(QueueProcessor::class, $queue, $worker);
    }

    public function execute()
    {
        return $this->queueProcessor->processBatch(static::BATCH_SIZE);
    }
}
