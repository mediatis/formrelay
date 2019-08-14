<?php

namespace Mediatis\Formrelay\Endpoint;

use Mediatis\Formrelay\Service\Registerable;

interface EndpointInterface extends Registerable
{
    public function beforeGateEvaluation($result, $data, $conf, $context);
    public function afterGateEvaluation($result, $data, $conf, $context);
    public function beforeDataMapping($result, $data, $conf, $context);
    public function afterDataMapping($result, $data, $conf, $context);
    public function dispatch($result, $data, $conf, $context);
}
