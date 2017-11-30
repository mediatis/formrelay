<?php
namespace Mediatis\Formrelay\Plugins;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Michael Vöhringer (Mediatis AG) <voehringer@mediatis.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use Mediatis\Formrelay\Service\FormrelayManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Utility\FormUtility;
use TYPO3\CMS\Form\PostProcess as Form;

class MailFormPostProcessor extends Form\AbstractPostProcessor implements Form\PostProcessorInterface
{

    /**
     * @var \Mediatis\Formrelay\Service\FormrelayManager
     */
    protected $FormrelayManager;

    /**
     * @var \TYPO3\CMS\Form\Domain\Model\Element
     */
    protected $form;

    /**
     * @var array
     */
    protected $formSettings;

    /**
     * Constructor
     *
     * @param \TYPO3\CMS\Form\Domain\Model\Element $form Form domain model
     * @param array $typoScript Post processor TypoScript formSettings
     */
    public function __construct(\TYPO3\CMS\Form\Domain\Model\Element $form, array $typoScript)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->FormrelayManager = GeneralUtility::makeInstance(FormrelayManager::class);

        $this->formSettings = $objectManager->get(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($typoScript);

        $this->form = $form;
    }

    /**
     * The main method called by the post processor
     *
     * process the data
     *
     * @return string HTML message from this processor
     */
    public function process()
    {
        $data = $this->getFormData();
        $this->FormrelayManager->process($data, $this->formSettings);
    }


    private function getFormData()
    {
        $data = array();
        return $this->loopData($this->form, $data);
    }

    private function loopData($formData, &$data)
    {
        $type = $formData->getElementType();
        $childElements = $formData->getChildElements();

        $plainElement = count($childElements) === 0 || $type === 'SELECT' || $type === 'RADIOGROUP';
        $noFormField = $type === 'HEADER' || $type === 'TEXTBLOCK';

        if ($noFormField) {
            // no data to gather
        } elseif ($plainElement) {
            $inputInformation = $formData->getAdditionalArguments();
            $name = $inputInformation['name'];
            $value = $inputInformation['value'];
            if ($type === 'CHECKBOX' && !$inputInformation['checked']) {
                $value = 0;
            }
            if ($type === 'RADIOGROUP') {
                $name = $formData->getName();
            }
            if ($type === 'FILEUPLOAD') {
                if(count($inputInformation['uploadedFiles']) && count($inputInformation['uploadedFiles'][0])) {
                    $value = $this->moveFileUpload($inputInformation['uploadedFiles'][0]);
                }
            }
            $data[$name] = $value;
        } else {
            foreach ($formData->getChildElements() as $input) {
                $this->loopData($input, $data);
            }
        }
        return $data;
    }
    
    private function moveFileUpload($file)
    {
        $url = '';
        if (isset($file['tempFilename']) && is_file($file['tempFilename']) && GeneralUtility::isAllowedAbsPath($file['tempFilename']))
        {    
            $fileName = $file['name'];

            // Safety checks
            $safetyChecked = true;
            $pathParts = pathinfo($fileName);
            // Check for prohibited file extensions
            if ($pathParts['extension'] === 'php') {
                $safetyChecked = false;
                GeneralUtility::devLog("Uploaded file did not pass safety checks, discarded", __CLASS__, 0);
            }
            
            if ($safetyChecked) {
                // Make sure base upload folder for this form exists
                $formRelaySettings = $this->FormrelayManager->getSettings();
                $baseUploadPath = $formRelaySettings['fileupload_path'] . $this->form->getId() . '/';
                if (!file_exists(PATH_site . $baseUploadPath)) {
                    GeneralUtility::mkdir_deep(PATH_site, $baseUploadPath);
                    GeneralUtility::devLog("Created Base upload folder for this form", __CLASS__, 0, $baseUploadPath);
                }
                
                // Create upload folder for this specific file
                $fileUploadPath = $baseUploadPath . sha1_file($file['tempFilename']) . '/';
                if (!file_exists(PATH_site . $fileUploadPath)) {
                    GeneralUtility::mkdir_deep(PATH_site, $fileUploadPath);
                }
                
                // Assemble full upload path and filename and move file
                $suffix = 1;
                while (file_exists(PATH_site . $fileUploadPath . $fileName)) {
                    $fileName = $pathParts['filename'] . '_' . $suffix . '.' . $pathParts['extension'];
                    $suffix++;
                }
                $result = rename($file['tempFilename'], PATH_site . $fileUploadPath . $fileName);
                if ($result) {
                    $url = rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/') . '/' . $fileUploadPath . $fileName;
                } else {
                    GeneralUtility::devLog('Failed to move uploaded file "' . $file['tempFilename'] . '" to destination "' . PATH_site . $fileUploadPath . $fileName . '"!', __CLASS__, 3);
                }
            }
        }
        return $url;
    }
}
