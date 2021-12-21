<?php

namespace Mediatis\Formrelay\Form\Element;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class JsonFieldElement extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement
{
    /**
     * Render textarea and use whitespaces to format JSON
     */
    public function render()
    {
        $parameterArray = $this->data['parameterArray'];
        $cols = $parameterArray['fieldConf']['config']['parameters']['cols'];
        $rows = $parameterArray['fieldConf']['config']['parameters']['rows'];
        $readOnly = $parameterArray['fieldConf']['config']['parameters']['readOnly'];

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        $fieldId = StringUtility::getUniqueId('formengine-textarea-');

        $attributes = [
            'id' => $fieldId,
            'name' => htmlspecialchars($parameterArray['itemFormElName']),
            'cols' => $cols,
            'rows' => $rows,
            'readonly' => $readOnly,
            'data-formengine-input-name' => htmlspecialchars($parameterArray['itemFormElName'])
        ];

        $classes = [
            'form-control',
            't3js-formengine-textarea',
            'formengine-textarea',
        ];
        // If value can be decoded into json, we encode it with JSON_PRETTY_PRINT, else use the raw value.
        $itemValue = json_decode($parameterArray['itemFormElValue']);
        if (!$itemValue) {
            $itemValue = $parameterArray['itemFormElValue'];
        } else {
            $itemValue = json_encode($itemValue, JSON_PRETTY_PRINT);
        }
        $attributes['class'] = implode(' ', $classes);

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] = $fieldInformationHtml;
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-element">';
        $html[] = '<div class="form-control-wrap">';
        $html[] = '<textarea ' . GeneralUtility::implodeAttributes($attributes, true) . '>';
        $html[] = $itemValue;
        $html[] = '</textarea>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);

        return $resultArray;
    }

}
