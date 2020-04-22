<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Exceptions;

use Exception;

class InvalidXmlFileException extends Exception
{

    public function __construct($file)
    {
        parent::__construct(sprintf('Bad filePath option ("%s")', $file), 1564825701);
    }
}
