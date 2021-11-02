<?php

namespace Mediatis\Formrelay\Scheduler;

use FormRelay\Core\Service\RegistryInterface;
use Mediatis\Formrelay\Domain\Repository\Queue\JobRepository;
use Mediatis\Formrelay\Factory\RegistryFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

abstract class QueueTask extends AbstractTask
{
    protected $pid = 0;

    /** @var RegistryInterface */
    protected $registry;

    protected function prepareTask()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var RegistryFactory $registryFactory */
        $registryFactory = $objectManager->get(RegistryFactory::class);

        $this->registry = $registryFactory->buildRegistry();

        /** @var JobRepository $queue */
        $queue = $this->registry->getQueue();
        $queue->setPid($this->pid);
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
