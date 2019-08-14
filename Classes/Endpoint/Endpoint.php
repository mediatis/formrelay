<?php

namespace Mediatis\Formrelay\Endpoint;

abstract class Endpoint implements EndpointInterface
{
    abstract protected function getExtensionKey();

    public function register(array $list)
    {
        array_push($list, $this->getExtensionKey());
        return [$list];
    }

    protected function proceed($extKey)
    {
        return $this->getExtensionKey() === $extKey;
    }

    public function beforeGateEvaluation($result, $data, $conf, $context)
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runBeforeGateEvaluation($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function afterGateEvaluation($result, $data, $conf, $context)
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runAfterGateEvaluation($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function beforeDataMapping($result, $data, $conf, $context)
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runBeforeDataMapping($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function afterDataMapping($result, $data, $conf, $context)
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runAfterDataMapping($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    public function dispatch($result, $data, $conf, $context)
    {
        if ($this->proceed($context['extKey'])) {
            return $this->runDispatch($result, $data, $conf, $context);
        }
        return [$result, $data, $conf, $context];
    }

    protected function runBeforeGateEvaluation($result, $data, $conf, $context)
    {
        return [$result, $data, $conf, $context];
    }

    protected function runAfterGateEvaluation($result, $data, $conf, $context)
    {
        return [$result, $data, $conf, $context];
    }

    protected function runBeforeDataMapping($result, $data, $conf, $context)
    {
        return [$result, $data, $conf, $context];
    }

    protected function runAfterDataMapping($result, $data, $conf, $context)
    {
        return [$result, $data, $conf, $context];
    }

    abstract protected function runDispatch($result, $data, $conf, $context);
}
