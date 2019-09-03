<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

interface FieldMapperInterface
{
    public function __construct($config = []);
    public function prepare(&$context, &$result);
    public function finish(&$context, &$result);
}
