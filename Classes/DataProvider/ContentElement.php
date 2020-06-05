<?php

namespace Mediatis\Formrelay\DataProvider;

use Mediatis\Formrelay\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class ContentElement implements DataProviderInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ConfigurationManager */
    protected $configurationManager;

    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Adds field with value from a content element to the E-Mail dataArray
     *
     * @param array $dataArray
     * @return void
     */
    public function addData(array &$dataArray)
    {
        $settings = $this->configurationManager->getExtensionSettings('tx_formrelay');
        $fieldName = $settings['dataProviders']['contentElement']['fieldName'];
        $ttContentUid = $settings['dataProviders']['contentElement']['ttContentUid'];

        $uids = strstr($ttContentUid, ',') ? explode(',', $ttContentUid) : [$ttContentUid];

        $contents = $this->prepareContents($uids);

        // Only add the data to the dataArray if the field is defined (not null)
        if (isset($dataArray[$fieldName])) {
            if ($dataArray[$fieldName] === '') {
                $dataArray[$fieldName] = $contents;
            } else {
                $dataArray[$fieldName] .= "\n" . $contents;
            }
        }
    }

    /**
     * Retrieves and prepares the contents
     *
     * @param array $uids
     * @return string
     */
    protected function prepareContents(array $uids)
    {
        $contents = '';
        $count = 0;

        foreach ($uids as $uid) {
            $content = $this->objectManager->get(RecordsContentObject::class)->render(
                [
                    'tables' => 'tt_content',
                    'source' => $uid,
                    'dontCheckPid' => 1
                ]
            );
            $content = $this->prettyContent($content);
            if (empty($content)) {
                continue;
            }
            $contents .= $count > 0 ? '\n' . $content : $content;
            $count++;
        }
        return $contents;
    }

    /**
     * Extract trimmed text from the rendered tt_content element
     *
     * @param string $content
     * @return string
     */
    protected function prettyContent($content)
    {
        return trim(strip_tags($content, '<a>'));
    }
}
