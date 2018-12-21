<?php

namespace Mediatis\Formrelay\Command;

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use Mediatis\Formrelay\Simulation\FormSimulator;

/**
 * CommandController for working with extension management through CLI/scheduler
 */
class FormSimulationCommandController extends CommandController
{

    /**
     * @var FormSimulator
     */
    protected $formSimulator;

    /**
     * @param \Mediatis\Formrelay\Simulation\FormSimulator $formSimulator
     */
    public function injectFormSimulator(FormSimulator $formSimulator)
    {
        $this->formSimulator = $formSimulator;
    }

    /**
     * Re-sends form data from a given log file.
     *
     * @param string $filePath
     * @param int $pageId
     * @return void
     * @cli
     */
    public function runCommand($filePath, $pageId = 1)
    {
        $this->formSimulator->run($filePath, $pageId);
    }
}
