<?php

namespace Mediatis\Formrelay\Destination;

use InvalidArgumentException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use Mediatis\Formrelay\DataDispatcher\DataDispatcherInterface;

abstract class AbstractDestination implements DestinationInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var Logger */
    protected $logger;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function initializeObject()
    {
        $logManager = $this->objectManager->get(LogManager::class);
        $this->logger = $logManager->getLogger(static::class);
    }

    public function register(array $list): array
    {
        array_push($list, $this->getExtensionKey());
        return [$list];
    }

    protected function proceed($extKey): bool
    {
        return $this->getExtensionKey() === $extKey;
    }

    public function beforeGateEvaluation($result, array $data, array $conf, array $context): array
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runBeforeGateEvaluation($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function afterGateEvaluation($result, array $data, array $conf, array $context): array
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runAfterGateEvaluation($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function beforeDataMapping($result, array $data, array $conf, array $context): array
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runBeforeDataMapping($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function afterDataMapping($result, array $data, array $conf, array $context): array
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runAfterDataMapping($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function dispatch($result, array $data, array $conf, array $context): array
    {
        if ($this->proceed($context['extKey'])) {
            $dispatcher = $this->getDispatcher($conf, $data, $context);
            if ($dispatcher !== null) {
                if (!($dispatcher instanceof DataDispatcherInterface)) {
                    throw new InvalidArgumentException(
                        'Error detected - "' . get_class($dispatcher) . '" must implement interface ' . DataDispatcherInterface::class . '.',
                        1566409832
                    );
                }
                $result = $dispatcher->send($data) || $result;
            }
        }
        return [$result, $data, $conf, $context];
    }

    protected function runBeforeGateEvaluation($result, array $data, array $conf, array $context): array
    {
        return [$result, $data, $conf, $context];
    }

    protected function runAfterGateEvaluation($result, array $data, array $conf, array $context): array
    {
        return [$result, $data, $conf, $context];
    }

    protected function runBeforeDataMapping($result, array $data, array $conf, array $context): array
    {
        return [$result, $data, $conf, $context];
    }

    protected function runAfterDataMapping($result, array $data, array $conf, array $context): array
    {
        return [$result, $data, $conf, $context];
    }

    /**
     * @param array $conf
     * @param array $data
     * @param array $context
     * @return DataDispatcherInterface|null
     */
    abstract protected function getDispatcher(array $conf, array $data, array $context);

    abstract protected function getExtensionKey(): string;
}
