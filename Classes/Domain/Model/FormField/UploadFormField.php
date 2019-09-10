<?php

namespace Mediatis\Formrelay\Domain\Model\FormField;

class UploadFormField implements FormFieldInterface
{
    /** @var string */
    protected $publicUrl;

    /** @var string */
    protected $relativePath;

    public function __construct(string $publicUrl, string $relativePath)
    {
        $this->publicUrl = $publicUrl;
        $this->relativePath = $relativePath;
    }

    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    public function __toString(): string
    {
        return $this->getPublicUrl();
    }
}
