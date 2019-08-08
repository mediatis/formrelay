<?php
declare(strict_types=1);

use Mediatis\Formrelay\Tests\Unit\FormrelayUnitTestCase;

class FormSimulatorUtilityTest extends FormrelayUnitTestCase
{
    /**
     * @test
     */
    public function initializeTsfeInitilaizesTypoScriptFrontEnd()
    {
        $pageId = 24;
        $language = 0;

        $this->buildTestCaseForTsfe($pageId);

        \Mediatis\Formrelay\Utility\FormSimulatorUtility::initializeTsfe(
            $pageId,
            $language,
            true
        );

        $this->assertSame(
            24,
            $GLOBALS['TSFE']->id
        );
    }
}
