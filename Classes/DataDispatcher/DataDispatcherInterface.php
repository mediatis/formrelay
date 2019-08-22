<?php

namespace Mediatis\Formrelay\DataDispatcher;

interface DataDispatcherInterface
{
    public function send(array $data): bool;
}
