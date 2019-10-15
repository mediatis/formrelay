<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use Mediatis\Formrelay\Domain\Model\FormField\UploadFormField;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;

class FileUploadElementProcessor extends ElementProcessor
{
    protected function getElementClass()
    {
        return FileUpload::class;
    }

    protected function process($element, $elementValue)
    {
        if ($elementValue === null) {
            return null;
        }

        if ($elementValue instanceof ExtbaseFileReference) {
            $elementValue = $elementValue->getOriginalResource();
        }

        if ($elementValue instanceof FileReference) {
            $elementValue = $elementValue->getOriginalFile();
        }

        $pluginTs = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
        if (!empty($pluginTs['settings.']['fileupload.']['prohibitedExtensions'])) {
            $prohibitedExtensions = explode(',', $pluginTs['settings.']['fileupload.']['prohibitedExtensions']);
            if (in_array($elementValue->getExtension(), $prohibitedExtensions)) {
                GeneralUtility::devLog(
                    "Uploaded file did not pass safety checks, discarded",
                    __CLASS__,
                    $elementValue->getExtension()
                );
                return null;
            }
        }
        $resourceFactory = ResourceFactory::getInstance();
        $defaultStorage = $resourceFactory->getDefaultStorage();

        $baseUploadPath = rtrim($this->options['baseUploadPath'], '/')
            . '/' . $element->getRootForm()->getIdentifier() . '/';
        $folderName = $elementValue->getSha1() . random_int(10000, 99999) . '/';

        $folderObject = $resourceFactory->createFolderObject(
            $defaultStorage,
            $baseUploadPath . $folderName,
            $folderName
        );

        try {
            $folder = $defaultStorage->getFolder($folderObject->getIdentifier());
        } catch (\Exception $e) {
            try {
                $folder = $defaultStorage->createFolder($folderObject->getIdentifier());
            } catch (\Exception $e) {
                GeneralUtility::devLog("UploadFormField folder for this form can not be created", __CLASS__, 0, $baseUploadPath);
                return null;
            }
        }

        $fileName = $elementValue->getName();
        $copiedFile = $elementValue->copyTo($folder);

        if ($copiedFile) {
            if ($copiedFile instanceof FileInterface) {
                $relativePath = $copiedFile->getPublicUrl();
                $publicUrl = trim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/') . '/' . $relativePath;
                return new UploadFormField($publicUrl, $relativePath);
            }
        } else {
            GeneralUtility::devLog(
                'Failed to copy uploaded file "' . $fileName . '" to destination "' . $folder->getIdentifier() . '"!',
                __CLASS__,
                3
            );
        }
        return null;
    }
}
