<?php

namespace Mediatis\Formrelay\DataProvider;

use Mediatis\Formrelay\Utility\FormrelayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class ContentElement implements \Mediatis\Formrelay\DataProviderInterface
{
    /**
     * Adds field with value from a content element to the E-Mail dataArray
     *
     * @param array $dataArray
     * @return void
     */
    public function addData(&$dataArray)
    {
        $settings = FormrelayUtility::loadPluginTS('tx_formrelay');
        $fieldName = $settings['settings.']['dataProviders.']['contentElement.']['fieldName'];
        $ttContentUid = $settings['settings.']['dataProviders.']['contentElement.']['ttContentUid'];

        $uids = strstr($ttContentUid, ',') ? explode(',', $ttContentUid) : [$ttContentUid];

        $contents = $this->prepareContents($uids);

        $dataArray[$fieldName] = $contents;
    }

    /**
     * Retrieves and prepares the contents
     *
     * @param array $uids
     * @return string
     */
    protected function prepareContents(array $uids): string
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class, __CLASS__);
        $contents = '';
        $count = 0;

        foreach ($uids as $uid) {
            $content = $objectManager->get(RecordsContentObject::class)->render(
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
    protected function prettyContent(string $content): string
    {
        return trim(strip_tags($content, '<a>'));
    }
}
