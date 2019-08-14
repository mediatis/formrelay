<?php

namespace Mediatis\Formrelay\Domain\Model;

class FormFieldUpload
{
    protected $value;

    public function __construct($value, $filePath)
    {
        $this->value = $value;
        $this->filePath = $filePath;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
