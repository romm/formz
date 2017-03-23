<?php
namespace Romm\Formz\Tests\Unit\Service;

use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidOptionValueException;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Service\TypoScriptService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class CacheServiceTest extends AbstractUnitTest
{
    /**
     * @test
     * @dataProvider backendCacheIsFetchedDataProvider
     * @param string $configurationValue
     * @param string $exception
     */
    public function backendCacheIsFetched($configurationValue, $exception = null)
    {
        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        /** @var TypoScriptService|\PHPUnit_Framework_MockObject_MockObject $typoScriptServiceMock */
        $typoScriptServiceMock = $this->getMockBuilder(TypoScriptService::class)
            ->setMethods(['getExtensionConfigurationFromPath'])
            ->getMock();

        $typoScriptServiceMock->expects($this->once())
            ->method('getExtensionConfigurationFromPath')
            ->willReturn($configurationValue);

        $cacheService = new CacheService;
        $cacheService->injectTypoScriptService($typoScriptServiceMock);

        $cacheService->getBackendCache();
    }

    /**
     * @return array
     */
    public function backendCacheIsFetchedDataProvider()
    {
        return [
            [
                'className' => 'nope!',
                'exception' => ClassNotFoundException::class
            ],
            [
                'className' => \stdClass::class,
                'exception' => InvalidOptionValueException::class
            ],
            [
                'className' => SimpleFileBackend::class
            ]
        ];
    }

    /**
     * @test
     */
    public function cacheInstanceIsCreatedOnce()
    {
        /** @var CacheManager|\PHPUnit_Framework_MockObject_MockObject $cacheManagerMock */
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->setMethods(['getCache'])
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('getCache')
            ->willReturnCallback(function () {
                return $this->getMockBuilder(FrontendInterface::class)
                    ->getMockForAbstractClass();
            });

        $cacheService = new CacheService;
        $cacheService->injectCacheManager($cacheManagerMock);

        $instance1 = $cacheService->getCacheInstance();
        $instance2 = $cacheService->getCacheInstance();

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     */
    public function clearCacheWorksProperly()
    {
        $files = [
            'foo/bar',
            'bar/baz',
            'baz/foo'
        ];

        /** @var CacheService|\PHPUnit_Framework_MockObject_MockObject $cacheService */
        $cacheService = $this->getMockBuilder(CacheService::class)
            ->setMethods(['getFilesInPath', 'clearFile'])
            ->getMock();

        $cacheService->expects($this->once())
            ->method('getFilesInPath')
            ->willReturn($files);

        $i = 0;
        foreach ($files as $file) {
            $cacheService->expects($this->at(++$i))
                ->method('clearFile')
                ->with($file);
        }

        $cacheService->clearCacheCommand(['cacheCmd' => 'all']);
    }
}
