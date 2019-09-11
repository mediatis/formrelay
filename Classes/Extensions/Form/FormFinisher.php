<?php

namespace Mediatis\Formrelay\Extensions\Form;

use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;
use Mediatis\Formrelay\Domain\Model\FormField\UploadFormField;
use Mediatis\Formrelay\Configuration\ConfigurationManager;
use Mediatis\Formrelay\Service\Relay;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\DatePicker;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class FormFinisher extends AbstractFinisher
{
    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var Relay */
    protected $relay;

    /** @var array */
    protected $defaultOptions = [
        'setup' => '',
        'baseUploadPath' => 'uploads/tx_formrelay/',
    ];

    protected $formValueMap = [];

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectRelay(Relay $relay)
    {
        $this->relay = $relay;
    }

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
            $typoScriptParser = $this->objectManager->get(TypoScriptParser::class);
            $typoScriptService = $this->objectManager->get(TypoScriptService::class);
            $typoScriptParser->parse($setup);
            $typoScript = $typoScriptParser->setup;
            $formSettings = $typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);
        } else {
            $formSettings = [];
        }

        $this->formValueMap = $this->finisherContext->getFormValues();
        $formRuntime = $this->finisherContext->getFormRuntime();

        $formValues = [];
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

            if ($element instanceof GenericFormElement) {
                $formValues[$name] = $this->processStandardField($element, $value);
            } elseif ($element instanceof DatePicker) {
                $formValues[$name] = $this->processDatePickerField($element, $value);
            } elseif ($element instanceof FileUpload) {
                /** @var FileInterface $uploadFieldValue */
                $uploadFieldValue = $this->processUploadField($element, $value);
                if ($uploadFieldValue !== null) {
                    $formValues[$name] = $uploadFieldValue;
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

        $this->relay->process($formValues, $formSettings, false);
    }

    protected function processStandardField(&$element, $value)
    {
        if ($element->getType() === 'Checkbox' && !$value) {
            $value = 0;
        }
        return is_array($value) ? new MultiValueFormField($value) : $value;
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
     * @return null|UploadFormField
     * @throws \Exception
     */
    protected function processUploadField(FormElementInterface $element, FileReference $file = null)
    {
        if ($file === null) {
            return null;
        }
        $file = $file->getOriginalResource()->getOriginalFile();

        $pluginTs = $this->configurationManager->getExtensionTypoScriptSetup('tx_formrelay');
        if (!empty($pluginTs['settings.']['fileupload.']['prohibitedExtensions'])) {
            $prohibitedExtensions = explode(',', $pluginTs['settings.']['fileupload.']['prohibitedExtensions']);
            if (in_array($file->getExtension(), $prohibitedExtensions)) {
                GeneralUtility::devLog(
                    "Uploaded file did not pass safety checks, discarded",
                    __CLASS__,
                    $file->getExtension()
                );
                return null;
            }
        }
        $resourceFactory = ResourceFactory::getInstance();
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
                GeneralUtility::devLog("UploadFormField folder for this form can not be created", __CLASS__, 0, $baseUploadPath);
                return null;
            }
        }

        $fileName = $file->getName();
        $copiedFile = $file->copyTo($folder);

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
