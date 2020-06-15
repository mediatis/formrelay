<?php

namespace Mediatis\Formrelay\Domain\Model\FormField;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UploadFormField implements FormFieldInterface
{
    /** @var FileInterface */
    protected $file;

    /** @var string */
    protected $fileName;

    public function __construct(FileInterface $file)
    {
        $this->file = $file;
        $this->fileName = $file->getName();
    }

    public function getFile(): FileInterface
    {
        return $this->file;
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

    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getMimeType(): string
    {
        return $this->file->getMimeType();
    }

    public function __toString(): string
    {
        return $this->getPublicUrl();
    }
}
