<?php

namespace Mediatis\Formrelay\Extensions\Form\ElementProcessor;

use Exception;
use FormRelay\Core\Model\Form\UploadField;
use Mediatis\Formrelay\Domain\Model\File\File;
use Mediatis\Formrelay\Utility\ArrayUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;

class FileUploadElementProcessor extends ElementProcessor
{
    /** @var array **/
    protected $pluginTs;

    protected function getElementClass()
    {
        return FileUpload::class;
    }

    protected function getPluginTs(): array
    {
        if (!$this->pluginTs) {
            $fullConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $configuration = $fullConfiguration['plugin.']['tx_formrelay.']['settings.'] ?? [];
            $this->pluginTs = ArrayUtility::convertTypoScriptArrayToPlainArray($configuration);
        }
        return $this->pluginTs;
    }

    public function processFormElement($element, $elementValue, array $options, array &$result, bool &$processed)
    {
        $this->options = $options;
        if ($this->match($element, $elementValue)) {
            $pluginTs = $this->getPluginTs();
            $name = $this->getElementName($element);
            if ($pluginTs['fileupload']['disableFileuploadProcessing']) {
                if (array_key_exists($name, $result)) {
                    unset($result[$name]);
                }
                $processed = true;
            } else {
                if (!$processed || $this->override()) {
                    $value = $this->process($element, $elementValue);
                    $result[$name] = $value;
                    $processed = true;
                }
            }
        }
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

        $pluginTs = $this->getPluginTs();

        if (!empty($pluginTs['fileupload']['prohibitedExtensions'])) {
            $prohibitedExtensions = explode(',', $pluginTs['fileupload']['prohibitedExtensions']);
            if (in_array($elementValue->getExtension(), $prohibitedExtensions)) {
                $this->logger->error(
                    'Uploaded file did not pass safety checks, discarded',
                    ['extension' => $elementValue->getExtension()]
                );
                return null;
            }
        }
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
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
        } catch (Exception $e) {
            try {
                $folder = $defaultStorage->createFolder($folderObject->getIdentifier());
            } catch (Exception $e) {
                $this->logger->error(
                    'UploadFormField folder for this form can not be created',
                    ['baseUploadPath' => $baseUploadPath]
                );
                return null;
            }
        }

        $fileName = $elementValue->getName();
        $copiedFile = $elementValue->copyTo($folder);

        if ($copiedFile) {
            if ($copiedFile instanceof FileInterface) {
                /** @var File $file */
                $file = GeneralUtility::makeInstance(File::class, $copiedFile);

                /** @var UploadField $uploadField */
                $uploadField = GeneralUtility::makeInstance(UploadField::class, $file);
                $uploadField->setFileName($fileName);
                return $uploadField;
            }
        } else {
            $this->logger->error(
                'Failed to copy uploaded file "' . $fileName . '" to destination "' . $folder->getIdentifier() . '"!',
                [
                    'fileName' => $fileName,
                    'destination' => $folder->getIdentifier(),
                ]
            );
        }
        return null;
    }
}
