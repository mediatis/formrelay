<?php

namespace Mediatis\Formrelay\Domain\Model\File;

use FormRelay\Core\Model\File\FileInterface;
use TYPO3\CMS\Core\Resource\FileInterface as Typo3FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class File implements FileInterface
{
    /** @var Typo3FileInterface $file */
    protected $file;

    public function __construct(Typo3FileInterface $file)
    {
        $this->file = $file;
    }

    public function getName(): string
    {
        return $this->file->getName();
    }

    public function getPublicUrl(): string
    {
        return trim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
            . '/'
            . $this->getRelativePath();
    }

    public function getRelativePath(): string
    {
        return $this->file->getPublicUrl();
    }

    public function getMimeType(): string
    {
        return $this->getMimeType();
    }
}
