<?php

namespace Mediatis\Formrelay\Plugins;

use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;
use Mediatis\Formrelay\Service\FormrelayManager;
use Mediatis\Formrelay\Utility\FormrelayUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\DatePicker;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class FormFinisher extends AbstractFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'setup' => '',
        'baseUploadPath' => 'uploads/tx_formrelay/',
		'addHiddenFields' => '',
    ];

    protected $formValueMap = [];

    protected function executeInternal()
    {
        $ignoreTypes = [
            'Page',
            'StaticText',
            'ContentElement',
            'Fieldset',
            'GridRow',
            'Honeypot',
        ];

        $setup = trim($this->parseOption('setup'));

        if ($setup) {
            $TSparserObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class
            );
            $TSparserObject->parse($setup);
            $typoscript = $TSparserObject->setup;
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $formSettings = $objectManager->get(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($typoscript);
        } else {
            $formSettings = [];
        }

        $this->formValueMap = $this->finisherContext->getFormValues();
        $formRuntime = $this->finisherContext->getFormRuntime();

        $formValues = [];
        $attachments = [];
        $elements = $formRuntime->getFormDefinition()->getRenderablesRecursively();
        /** @var AbstractFormElement $element */
        foreach ($elements as $element) {
            $type = $element->getType();

            if (in_array($type, $ignoreTypes)) {
                continue;
            }

            $id = $element->getIdentifier();
            $name = $element->getProperties()['fluidAdditionalAttributes']['name'] ?: $id;
            $value = $this->formValueMap[$id];

            if (($element instanceof GenericFormElement)) {
                $formValues[$name] = $this->processStandardField($element, $value);
            } elseif ($element instanceof DatePicker) {
                $formValues[$name] = $this->processDatePickerField($element, $value);
            } elseif ($element instanceof FileUpload) {
                /** @var FileInterface $copiedFile */
                $copiedFile = $this->processUploadField($element, $value);
                if ($copiedFile instanceof FileInterface) {
                    $publicUrl = $copiedFile->getPublicUrl();
                    $attachments[] = $publicUrl;
                    $formValues[$name] = trim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/') . '/' . $publicUrl;
                }
            } else {
                GeneralUtility::devLog(
                    'Ignoring unkonwn form field type.',
                    __CLASS__,
                    0,
                    [
                        'form' => $element->getRootForm()->getIdentifier(),
                        'field' => $name,
                        'class' => get_class($element),
                        'type' => $type,
                    ]
                );
            }
        }

        // Add hidden fields (if configured in form or plugin) to form data
        $hiddenFields = trim($this->parseOption('addHiddenFields'));
        if (trim($hiddenFields) != '' ) {
            $hiddenFieldsArray = explode(PHP_EOL, $hiddenFields);
            foreach ($hiddenFieldsArray as $hiddenField) {
                $field = explode(':', $hiddenField);
                if(count($field) > 1) {
                    $field_name = trim($field[0]);
                    $field_value = trim($field[1]);
                    if($field_name != '' && $field_value != '') {
                        $formValues[$field_name] = $field_value;
                    }
                }
            }
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $formrelayManager = $objectManager->get(FormrelayManager::class);

        $formrelayManager->process($formValues, $formSettings, false, $attachments);
    }

    protected function processStandardField(&$element, $value)
    {
        if ($element->getType() === 'Checkbox' && !$value) {
            $value = 0;
        }
        return is_array($value) ? new FormFieldMultiValue($value) : $value;
    }

    protected function processDatePickerField(&$element, $dateObject)
    {
        $value = '';
        $properties = $element->getProperties();
        if ($dateObject instanceof \DateTime) {
            if (isset($properties['dateFormat'])) {
                $dateFormat = $properties['dateFormat'];
                if (isset($properties['displayTimeSelector']) && $properties['displayTimeSelector'] === true) {
                    $dateFormat .= ' H:i';
                }
            } else {
                $dateFormat = \DateTime::W3C;
            }
            $value = $dateObject->format($dateFormat);
        }
        return $value;
    }

    /**
     * @param FormElementInterface $element
     * @param FileReference|null $file
     * @return null|file
     * @throws \Exception
     */
    protected function processUploadField(FormElementInterface $element, FileReference $file = null)
    {
        if ($file === null) {
            return '';
        }

        $file = $file->getOriginalResource()->getOriginalFile();

        $pluginTs = FormrelayUtility::loadPluginTS('tx_formrelay');
        if (!empty($pluginTs['settings.']['fileupload.']['prohibitedExtensions'])) {
            $prohibitedExtensions = explode(',', $pluginTs['settings.']['fileupload.']['prohibitedExtensions']);
            if (in_array($file->getExtension(), $prohibitedExtensions)) {
                GeneralUtility::devLog(
                    "Uploaded file did not pass safety checks, discarded",
                    __CLASS__,
                    $file->getExtension()
                );
                return '';
            }
        }
        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $defaultStorage = $resourceFactory->getDefaultStorage();

        $baseUploadPath = rtrim($this->parseOption('baseUploadPath'), '/') .
            '/' . $element->getRootForm()->getIdentifier() . '/';
        $folderName = $file->getSha1() . random_int(10000, 99999) . '/';

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
                GeneralUtility::devLog("Upload folder for this form can not be created", __CLASS__, 0, $baseUploadPath);
                return '';
            }
        }

        $fileName = $file->getName();
        $copiedFile = $file->copyTo($folder);

        if ($copiedFile) {
            return $copiedFile;
        } else {
            GeneralUtility::devLog(
                'Failed to copy uploaded file "' . $fileName . '" to destination "' . $folder->getIdentifier() . '"!',
                __CLASS__,
                3
            );
        }
    }
}
