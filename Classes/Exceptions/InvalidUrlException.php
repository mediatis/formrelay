<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Exceptions;

use Exception;

class InvalidUrlException extends Exception
{

    public function __construct($url)
    {
        parent::__construct(sprintf('Bad URL %s', $url), 1565612422);
    }
}
