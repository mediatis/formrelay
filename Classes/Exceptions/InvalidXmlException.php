<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Exceptions;

use Exception;

class InvalidXmlException extends Exception
{

    public function __construct($message)
    {
        parent::__construct(sprintf('Bad xml %s', $message), 1565092401);
    }
}
