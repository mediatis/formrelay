<?php

namespace Mediatis\Formrelay\DataProvider;

use FormRelay\Core\DataProvider\DataProvider;
use FormRelay\Core\Model\Submission\SubmissionInterface;
use FormRelay\Core\Service\RegistryInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\RecordsContentObject;

class ContentElementDataProvider extends DataProvider
{
    const KEY_FIELD = 'field';
    const DEFAULT_FIELD = '';

    const KEY_CONTENT_ID = 'ttContentUid';
    const DEFAULT_CONTENT_ID = 0;

    /** @var ObjectManager */
    protected $objectManager;

    public function __construct(RegistryInterface $registry, ObjectManager $objectManager)
    {
        parent::__construct($registry);
        $this->objectManager = $objectManager;
    }

    protected function processContext(SubmissionInterface $submission)
    {
        $ttContentUid = $this->getConfig(static::KEY_CONTENT_ID, static::DEFAULT_CONTENT_ID);

        $uids = strstr($ttContentUid, ',') ? explode(',', $ttContentUid) : [$ttContentUid];

        $content = $this->renderContentElements($uids);
        if ($content) {
            $this->addToContext($submission, 'content_element', $content);
        }
    }

    protected function process(SubmissionInterface $submission)
    {
        $field = $this->getConfig(static::KEY_FIELD, static::DEFAULT_FIELD);
        if ($field) {
            $this->appendToFieldFromContext($submission, 'content_element', $field, "\n");
        }
    }

    /**
     * Retrieves and prepares the contents
     *
     * @param array $uids
     * @return string
     */
    protected function renderContentElements(array $uids)
    {
        $content = '';
        $count = 0;

        foreach ($uids as $uid) {
            $renderedElement = $this->objectManager->get(RecordsContentObject::class)->render(
                [
                    'tables' => 'tt_content',
                    'source' => $uid,
                    'dontCheckPid' => 1
                ]
            );
            $renderedElement = $this->prettyContent($renderedElement);
            if (empty($renderedElement)) {
                continue;
            }
            $content .= $count > 0 ? '\n' . $renderedElement : $renderedElement;
            $count++;
        }
        return $content;
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

    public static function getDefaultConfiguration(): array
    {
        return [
            static::KEY_ENABLED => static::DEFAULT_ENABLED,
            static::KEY_MUST_EXIST => static::DEFAULT_MUST_EXIST,
            static::KEY_FIELD => static::DEFAULT_FIELD,
            static::KEY_CONTENT_ID => static::DEFAULT_CONTENT_ID,
        ];
    }


}
