<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractSection;

class AbstractSectionElementProcessor extends IgnoredElementProcessor
{
    protected function getElementClass()
    {
        return AbstractSection::class;
    }
}
