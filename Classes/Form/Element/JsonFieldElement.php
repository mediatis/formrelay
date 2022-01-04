<?php

namespace Mediatis\Formrelay\Form\Element;

class JsonFieldElement extends \TYPO3\CMS\Backend\Form\Element\TextElement
{
    /**
     * Render textarea and use whitespaces to format JSON
     */
    public function render()
    {
        $parameterArray = $this->data['parameterArray'];
        // If value can be decoded into json, we encode it again with JSON_PRETTY_PRINT
        $itemValue = json_decode($parameterArray['itemFormElValue']);
        if ($itemValue) {
            $this->data['parameterArray']['itemFormElValue'] = json_encode($itemValue, JSON_PRETTY_PRINT);
        }
        return parent::render();
    }
}
