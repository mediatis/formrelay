<?php

namespace Mediatis\Tests\Unit\ConfigurationResolver\ContentResolver;

use Mediatis\Formrelay\ConfigurationResolver\ContentResolver\FieldContentResolver;
use Mediatis\Formrelay\Domain\Model\FormField\MultiValueFormField;

class FieldContentResolverTest extends AbstractContentResolverTest
{

    protected function getSubjectClass()
    {
        return FieldContentResolver::class;
    }

    public function testExistingField()
    {
        $this->createTest('someField');
        $this->performTest(
            [
                'data' => ['someField' => 'someValue']
            ],
            'someValue'
        );
    }

    public function testNonExistingField()
    {
        $this->createTest('someField');
        $this->performTest(
            [
                'data' => ['someOtherField' => 'someValue']
            ],
            ''
        );
    }

    public function testMultiValueField()
    {
        $this->createTest('someField');
        $this->performTest(
            [
                'data' => ['someField' => new MultiValueFormField(['a', 'b'])]
            ],
            'a,b'
        );
    }

    public function testMultiValueFieldWithGlue()
    {
        $this->createTest('someField');
        $this->performTest(
            [
                'glue' => ':',
                'data' => ['someField' => new MultiValueFormField(['a', 'b'])]
            ],
            'a:b'
        );
    }
}
