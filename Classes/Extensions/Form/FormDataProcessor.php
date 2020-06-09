<?php

namespace Mediatis\Formrelay\Extensions\Form;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class FormDataProcessor
{
    const SIGNAL_PROCESS_FORM_ELEMENT = 'processFormElement';

    /** @var Logger */
    protected $logger;

    /** @var Dispatcher */
    protected $signalSlotDispatcher;

    public function injectLogger(LogManager $logManager)
    {
        $this->logger = $logManager->getLogger(static::class);
    }

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function process($elements, $values, $options)
    {
        $result = [];
        foreach ($elements as $element) {
            $type = $element->getType();
            $id = $element->getIdentifier();
            $value = $values[$id] ?? null;

            $processed = false;
            // default element processors are within
            // the namespace \Mediatis\Formrelay\Extensions\Form\ElementProcessor
            $this->signalSlotDispatcher->dispatch(
                self::class,
                static::SIGNAL_PROCESS_FORM_ELEMENT,
                [$element, $value, $options, &$result, &$processed]
            );
            if (!$processed) {
                $this->logger->error('Ignoring unknown form field type.', [
                    'form' => $element->getRootForm()->getIdentifier(),
                    'field' => $id,
                    'class' => get_class($element),
                    'type' => $type,
                ]);
            }
        }
        return $result;
    }
}
