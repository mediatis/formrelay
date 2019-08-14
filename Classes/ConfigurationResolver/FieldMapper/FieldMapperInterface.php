<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

interface FieldMapperInterface
{
    public function __construct($config = []);
    public function prepare(&$result, &$context);
    public function finish(&$result, &$context);
}
