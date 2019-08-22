<?php

namespace Mediatis\Formrelay\Service;

use InvalidArgumentException;
use Mediatis\Formrelay\DataProvider\DataProviderInterface;
use Mediatis\Formrelay\ConfigurationResolver\Evaluation\Evaluation;
use Mediatis\Formrelay\ConfigurationResolver\Evaluation\EvaluationInterface;
use Mediatis\Formrelay\Destination\DestinationInterface;
use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\FieldMapper;
use Mediatis\Formrelay\ConfigurationResolver\FieldMapper\FieldMapperInterface;
use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\ValueMapper;
use Mediatis\Formrelay\ConfigurationResolver\ValueMapper\ValueMapperInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class Registry implements SingletonInterface
{
    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    protected function register($classReference, $interfaceReference, $signalClass, $signals, $registerable = false)
    {
        $implementedInterfaces = class_implements($classReference);
        if (!in_array($interfaceReference, $implementedInterfaces)) {
            throw new InvalidArgumentException(
                'Error detected - "' . $classReference . '" must implement interface ' . $interfaceReference . '.',
                1565086200
            );
        }
        if ($registerable && !in_array(Registerable::class, $implementedInterfaces)) {
            throw new InvalidArgumentException(
                'Error detected - "' . $classReference . '" must implement interface ' . Registerable::class . '.',
                1565156253
            );
        }
        foreach ($signals as $signal) {
            $this->signalSlotDispatcher->connect($signalClass, $signal, $classReference, $signal);
        }
    }

    public function registerDestination(string $classReference)
    {
        $this->register(
            $classReference,
            DestinationInterface::class,
            Relay::class,
            [
                Relay::SIGNAL_REGISTER,
                Relay::SIGNAL_BEFORE_GATE_EVALUATION,
                Relay::SIGNAL_AFTER_GATE_EVALUATION,
                Relay::SIGNAL_BEFORE_DATA_MAPPING,
                Relay::SIGNAL_AFTER_DATA_MAPPING,
                Relay::SIGNAL_DISPATCH,
            ],
            true
        );
    }

    public function registerDataProvider(string $classReference)
    {
        $this->register(
            $classReference,
            DataProviderInterface::class,
            Relay::class,
            [Relay::SIGNAL_ADD_DATA]
        );
    }

    public function registerEvaluation(string $classReference)
    {
        $this->register(
            $classReference,
            EvaluationInterface::class,
            Evaluation::class,
            [Evaluation::SIGNAL_REGISTER],
            true
        );
    }

    public function registerFieldMapper(string $classReference)
    {
        $this->register(
            $classReference,
            FieldMapperInterface::class,
            FieldMapper::class,
            [Fieldmapper::SIGNAL_REGISTER],
            true
        );
    }

    public function registerValueMapper(string $classReference)
    {
        $this->register(
            $classReference,
            ValueMapperInterface::class,
            ValueMapper::class,
            [ValueMapper::SIGNAL_REGISTER],
            true
        );
    }
}
