<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Queue\QueueInterface;
use FormRelay\Core\Service\Registry;
use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\Domain\Repository\Queue\JobRepository;
use Mediatis\Formrelay\Request\Typo3Request;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class RegistryFactory
{
    const SIGNAL_UPDATE_REGISTRY = 'update';

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    /** @var LogManagerInterface */
    protected $logManager;

    /** @var QueueInterface */
    protected $queue;

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectLogManager(LogManagerInterface $logManager)
    {
        $this->logManager = $logManager;
    }

    public function injectJobRepository(JobRepository $jobRepository)
    {
        $this->queue = $jobRepository;
    }

    public function buildRegistry(): RegistryInterface
    {
        $request = new Typo3Request();
        $loggerFactory = new LoggerFactory($this->logManager);
        $queueDataFactory = new QueueDataFactory();
        $registry = new Registry($request, $loggerFactory, $this->queue, null, $queueDataFactory);
        $this->signalSlotDispatcher->dispatch(RegistryInterface::class, static::SIGNAL_UPDATE_REGISTRY, [$registry]);
        return $registry;
    }
}
