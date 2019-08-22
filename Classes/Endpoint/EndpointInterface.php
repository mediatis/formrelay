<?php

namespace Mediatis\Formrelay\Endpoint;

use Mediatis\Formrelay\Service\Registerable;

interface EndpointInterface extends Registerable
{
    /**
     * @param bool|null $result
     * @param array $data
     * @param array $conf
     * @param array $context
     * @return array
     */
    public function beforeGateEvaluation($result, array $data, array $conf, array $context): array;

    /**
     * @param bool|null $result
     * @param array $data
     * @param array $conf
     * @param array $context
     * @return array
     */
    public function afterGateEvaluation($result, array $data, array $conf, array $context): array;

    /**
     * @param bool|null $result
     * @param array $data
     * @param array $conf
     * @param array $context
     * @return array
     */
    public function beforeDataMapping($result, array $data, array $conf, array $context): array;

    /**
     * @param bool|null $result
     * @param array $data
     * @param array $conf
     * @param array $context
     * @return array
     */
    public function afterDataMapping($result, array $data, array $conf, array $context): array;

    /**
     * @param bool|null $result
     * @param array $data
     * @param array $conf
     * @param array $context
     * @return array
     */
    public function dispatch($result, array $data, array $conf, array $context): array;
}
