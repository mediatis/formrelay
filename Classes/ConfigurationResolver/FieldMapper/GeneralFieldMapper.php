<?php

namespace Mediatis\Formrelay\ConfigurationResolver\FieldMapper;

class GeneralFieldMapper extends FieldMapper
{
    protected $fieldMappers = [];

    protected function getKeyword()
    {
        return '';
    }

    public function process(&$result, $context)
    {
        $this->fieldMappers = [];
        if (!is_array($this->config)) {
            $fieldMapper = $this->objectManager->get(PlainFieldMapper::class, $this->config);
            $this->fieldMappers[] = $fieldMapper;
        } else {
            // plain values will be computed last and thus be added to the fieldMapper list first
            if (isset($this->config['_typoScriptNodeValue'])) {
                $fieldMapper = $this->objectManager->get(PlainFieldMapper::class, $this->config['_typoScriptNodeValue']);
                $this->fieldMappers[] = $fieldMapper;
            }

            // all other mappers are called in the reverse order of their appearance (last mapper wins)
            foreach ($this->config as $key => $value) {
                if ($key === '_typoScriptNodeValue' || $key === 'if') {
                    continue;
                }
                $fieldMapper = $this->resolveKeyword($key, $value);
                if ($fieldMapper) {
                    $this->fieldMappers[] = $fieldMapper;
                }
            }

            // if-constructs will be computed first and thus be added to the fieldMapper list last
            if (isset($this->config['if'])) {
                $fieldMapper = $this->resolveKeyword('if', $this->config['if']);
                if ($fieldMapper) {
                    $this->fieldMappers[] = $fieldMapper;
                }
            }
        }
        $this->prepare($result, $context);
        $this->finish($result, $context);
    }

    public function prepare(&$result, &$context) {
        foreach ($this->fieldMappers as $fieldMapper) {
            $fieldMapper->prepare($result, $context);
        }
    }

    public function finish(&$result, &$context)
    {
        foreach (array_reverse($this->fieldMappers) as $fieldMapper) {
            if ($fieldMapper->finish($result, $context)) {
                break;
            }
        }
        return true;
    }
}
