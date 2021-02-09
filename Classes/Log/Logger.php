<?php

namespace Mediatis\Formrelay\Log;

use FormRelay\Core\Log\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class Logger implements LoggerInterface
{
    protected $logger;

    public function __construct(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function debug(string $msg)
    {
        $this->logger->debug($msg);
    }

    public function info(string $msg)
    {
        $this->logger->info($msg);
    }

    public function warning(string $msg)
    {
        $this->logger->warning($msg);
    }

    public function error(string $msg)
    {
        $this->logger->error($msg);
    }
}
