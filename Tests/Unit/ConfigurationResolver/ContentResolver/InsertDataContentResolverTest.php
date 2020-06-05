<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ContentResolver\InsertDataContentResolver;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class InsertDataContentResolverTest extends AbstractContentResolverTest
{

    protected function getSubjectClass()
    {
        return InsertDataContentResolver::class;
    }

    public function testNoInsert()
    {
        $this->createTest(false);
        $this->performTest(
            [
                'data' => ['someField' => 'someValue'],
            ],
            '{someField}',
            '{someField}'
        );
    }

    public function testExistingField()
    {
        $this->createTest(true);
        $this->performTest(
            [
                'data' => ['someField' => 'someValue']
            ],
            'someValue',
            '{someField}'
        );
    }

    public function testExistingFieldEmbedded()
    {
        $this->createTest(true);
        $this->performTest(
            [
                'data' => ['someField' => 'someValue']
            ],
            'abcsomeValuedef',
            'abc{someField}def'
        );
    }

    public function testNonExistingField()
    {
        $this->createTest(true);
        $this->performTest(
            [
                'data' => ['someOtherField' => 'someValue']
            ],
            '',
            '{someField}'
        );
    }

    public function testMultiValueField()
    {
        $this->createTest(true);
        $this->performTest(
            [
                'data' => ['someField' => new MultiValueFormField(['a', 'b'])]
            ],
            'a,b',
            '{someField}'
        );
    }

    public function testMultiValueFieldWithGlue()
    {
        $this->createTest(true);
        $this->performTest(
            [
                'glue' => ':',
                'data' => ['someField' => new MultiValueFormField(['a', 'b'])]
            ],
            'a:b',
            '{someField}'
        );
    }
}
