<?php
declare(strict_types=1);

namespace Mediatis\Formrelay\Tests\Unit;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class FormrelayUnitTestCase
 * @package Mediatis\Formrelay\Tests\Unit
 */
abstract class FormrelayUnitTestCase extends UnitTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        GeneralUtility::resetSingletonInstances([]);
        unset($GLOBALS['TSFE']);
    }

    /**
     * @param int $pageId
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function buildTestCaseForTsfe(int $pageId)
    {
        /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend|\Prophecy\Prophecy\ObjectProphecy $frontendCache */
        $frontendCache = $this->prophesize(\TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class);
        /** @var \TYPO3\CMS\Core\Cache\CacheManager|\Prophecy\Prophecy\ObjectProphecy $cacheManager */
        $cacheManager = $this->prophesize(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cacheManager
            ->getCache('cache_pages')
            ->willReturn($frontendCache->reveal());
        $cacheManager
            ->getCache('cache_runtime')
            ->willReturn($frontendCache->reveal());
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Cache\CacheManager::class, $cacheManager->reveal());

        /** @var PageRepository|\Prophecy\Prophecy\ObjectProphecy $pageRepository */
        $pageRepository = $this->prophesize(PageRepository::class);
        GeneralUtility::addInstance(PageRepository::class, $pageRepository->reveal());

        /** @var ExtendedTemplateService|\Prophecy\Prophecy\ObjectProphecy $extendedTemplateService */
        $extendedTemplateService = $this->prophesize(ExtendedTemplateService::class);
        GeneralUtility::addInstance(ExtendedTemplateService::class, $extendedTemplateService->reveal());

        /** @var TypoScriptFrontendController|\Prophecy\Prophecy\ObjectProphecy $tsfeProphecy */
        $tsfeProphecy = $this->prophesize(TypoScriptFrontendController::class);
        $tsfeProphecy->willBeConstructedWith([null, $pageId, 0]);
        $tsfe = $tsfeProphecy->reveal();
        $tsfe->tmpl = new \TYPO3\CMS\Core\TypoScript\TemplateService();
        GeneralUtility::addInstance(TypoScriptFrontendController::class, $tsfe);
    }
}
