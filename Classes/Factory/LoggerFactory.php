<?php

namespace Mediatis\Formrelay\Factory;

use FormRelay\Core\Factory\LoggerFactoryInterface;
use FormRelay\Core\Log\LoggerInterface;
use Mediatis\Formrelay\Log\Logger;
use TYPO3\CMS\Core\Log\LogManagerInterface;

class LoggerFactory implements LoggerFactoryInterface
{
    protected $logManager;

    public function __construct(LogManagerInterface $logManager)
    {
        $this->logManager = $logManager;
    }

    public function getLogger(string $forClass): LoggerInterface
    {
        return new Logger($this->logManager->getLogger($forClass));
    }
}
