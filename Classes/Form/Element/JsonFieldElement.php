<?php

namespace Mediatis\Formrelay\Form\Element;

use TYPO3\CMS\Backend\Form\Element\TextElement;

class JsonFieldElement extends TextElement
{
    /**
     * Render textarea and use whitespaces to format JSON
     */
    public function render()
    {
        // If value can be decoded into json, we encode it again with JSON_PRETTY_PRINT
        $itemValue = json_decode($this->data['parameterArray']['itemFormElValue']);
        if ($itemValue) {
            $this->data['parameterArray']['itemFormElValue'] = json_encode($itemValue, JSON_PRETTY_PRINT);
        }
        return parent::render();
    }
}
