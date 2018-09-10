<?php
namespace Mediatis\Formrelay\Plugins;

use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\AdvancedPassword;
use TYPO3\CMS\Form\Domain\Model\FormElements\DatePicker;

use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Utility\FormUtility;

use Mediatis\Formrelay\Service\FormrelayManager;
use Mediatis\Formrelay\Domain\Model\FormFieldMultiValue;

class FormFinisher extends AbstractFinisher
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'setup' => '',
        'deleteUploadedFiles' => false,
    ];

    protected $formValueMap = array();

    // THIS METHOD IS NOT WORKING YET!
    protected function processUploadField(&$element, $file, &$formrelayManager)
    {
        if (!$file) {
            return '';
        }
        if ($file instanceof FileReference) {
            $file = $file->getOriginalResource();
        }
        $file = $file->getOriginalFile();

        if ($file->isMissing()) {
            return '';
        }

        $deleteUploadedFiles = $this->parseOption('deleteUploadedFiles');
        $formRelaySettings = $formrelayManager->getSettings();

        $prohibitedExtensions = explode(',', $formRelaySettings['fileupload.']['prohibitedExtensions']);
        if (in_array($file->getExtension(), $prohibitedExtensions)) {
            GeneralUtility::devLog("Uploaded file did not pass safety checks, discarded", __CLASS__, $filter->getExtension());
            $file->getStorage()->deleteFile($file);
            return '';
        }

        // print_r(array(
        //     'file' => get_class_methods($file),
        //     'element' => get_class_methods($element),
        // ));
        // exit;

        // Make sure base upload folder for this form exists
        $baseUploadPath = 'uploads/tx_formrelay/' . $element->getRootForm()->getIdentifier() . '/';
        if (!file_exists(PATH_site . $baseUploadPath)) {
            GeneralUtility::mkdir_deep(PATH_site, $baseUploadPath);
            GeneralUtility::devLog("Created Base upload folder for this form", __CLASS__, 0, $baseUploadPath);
        }

        // Create upload folder for this specific file
        $fileUploadPath = $baseUploadPath . $file->getSha1() . random_int(10000, 99999) . '/';
        if (!file_exists(PATH_site . $fileUploadPath)) {
            GeneralUtility::mkdir_deep(PATH_site, $fileUploadPath);
        }

        // Assemble full upload path and filename and move file
        $suffix = 1;
        $fileName = $file->getName();
        while (file_exists(PATH_site . $fileUploadPath . $fileName)) {
            $fileName = $file->getNameWithoutExtension() . '_' . $suffix . '.' . $file->getExtension();
            $suffix++;
        }

        $localPath = PATH_site . $fileUploadPath;

        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $defaultStorage = $resourceFactory->getDefaultStorage();
        // @TODO the following command seems to fail
        // i guess because all paths are meant to be relative to the storage's root folder
        // but i a m not sure
        $folder = $defaultStorage->getFolder($localPath);

        if ($deleteUploadedFiles) {
            $result = $file->moveTo($localPath);
        } else {
            $result = $file->copyTo($localPath);
        }

        if ($result) {
            return $rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/') . '/' . $fileUploadPath . $fileName;
        } else {
            GeneralUtility::devLog('Failed to ' . ($deleteUploadedFiles ? 'move' : 'copy') . ' uploaded file "' . $fileName . '" to destination "' . $localPath . '"!', __CLASS__, 3);
        }
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

    protected function processStandardField(&$element, $value)
    {
        if ($element->getType() === 'Checkbox' && !$value) {
            $value = 0;
        }
        return is_array($value) ? new FormFieldMultiValue($value) : $value;
    }

    protected function executeInternal()
    {
        $formrelayManager = GeneralUtility::makeInstance(FormrelayManager::class);
        $ignoreTypes = array(
            'Page',
            'StaticText',
            'ContentElement',
            'Fieldset',
            'GridRow',
            'Honeypot',
        );

        $setup = trim($this->parseOption('setup'));

        if ($setup) {
            $TSparserObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class);
            $TSparserObject->parse($setup);
            $typoscript = $TSparserObject->setup;
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $formSettings = $objectManager->get(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($typoscript);
        } else {
            $formSettings = array();
        }

        $this->formValueMap = $this->finisherContext->getFormValues();
        $formRuntime = $this->finisherContext->getFormRuntime();

        $formValues = array();

        $elements = $formRuntime->getFormDefinition()->getRenderablesRecursively();
        $classes = array();
        foreach ($elements as $element) {
            $type = $element->getType();

            if (in_array($type, $ignoreTypes)) {
                continue;
            }

            $id = $element->getIdentifier();
            $name = $element->getProperties()['fluidAdditionalAttributes']['name'] ?: $id;
            $value = $this->formValueMap[$id];

            if (($element instanceof GenericFormElement) || ($element instanceof AdvancedPassword)) {
                $formValues[$name] = $this->processStandardField($element, $value);
            } elseif ($element instanceof DatePicker) {
                $formValues[$name] = $this->processDatePickerField($element, $value);
            } elseif ($element instanceof FileUpload) {
                // @TODO finish implementation of the method processUploadField
                // $formValues[$name] = $this->processUploadField($element, $value, $formrelayManager);
                GeneralUtility::devLog('Ignoring upload form field (currently unsupported).', __CLASS__, 0, array(
                    'form' => $element->getRootForm()->getIdentifier(),
                    'field' => $name,
                ));
            } else {
                GeneralUtility::devLog('Ignoring unkonwn form field type.', __CLASS__, 0, array(
                    'form' => $element->getRootForm()->getIdentifier(),
                    'field' => $name,
                    'class' => get_class($element),
                    'type' => $type,
                ));
            }
        }

        $formrelayManager->process($formValues, $formSettings);
    }
}
