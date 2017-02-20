<?php
namespace Romm\Formz\Tests\Unit\Condition\Processor;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\Formz\Condition\Processor\ConditionProcessor;
use Romm\Formz\Condition\Processor\ConditionProcessorFactory;
use Romm\Formz\Service\CacheService;
use Romm\Formz\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ConditionProcessorFactoryTest extends AbstractUnitTest
{
    /**
     * Checks that the factory creation method return the same instance, which
     * is created only once.
     *
     * @test
     */
    public function conditionProcessorIsCreatedOnlyOnce()
    {
        $dummyClass = new \stdClass;

        /** @var ConditionProcessorFactory|\PHPUnit_Framework_MockObject_MockObject $conditionProcessorFactoryMock */
        $conditionProcessorFactoryMock = $this->getMockBuilder(ConditionProcessorFactory::class)
            ->setMethods(['fetchProcessorInstanceFromCache'])
            ->getMock();

        $conditionProcessorFactoryMock->expects($this->once())
            ->method('fetchProcessorInstanceFromCache')
            ->willReturn($dummyClass);

        $formObject = $this->getFormObject();
        $conditionProcessor1 = $conditionProcessorFactoryMock->get($formObject);
        $conditionProcessor2 = $conditionProcessorFactoryMock->get($formObject);

        $this->assertSame($conditionProcessor1, $conditionProcessor2);
    }

    /**
     * Check that the condition processor is stored in cache after being
     * created. Also checks that the correct methods are called.
     *
     * @test
     */
    public function conditionProcessorIsStoredInCache()
    {
        // Mock that will be returned by the factory.
        $conditionProcessorMock = $this->getMockBuilder(ConditionProcessor::class)
            ->setMethods(['calculateAllTrees'])
            ->disableOriginalConstructor()
            ->getMock();

        $conditionProcessorMock->expects($this->once())
            ->method('calculateAllTrees');

        // Cache identifier used by the cache manager.
        $cacheIdentifier = 'some-cache-identifier';

        // Creating a prophecy of the cache instance used in the factory.
        /** @var TransientMemoryBackend|ObjectProphecy $transientMemoryProphecy */
        $transientMemoryProphecy = $this->prophet->prophesize(TransientMemoryBackend::class);

        $transientMemoryProphecy->has($cacheIdentifier)
            ->shouldBeCalled()
            ->willReturn(false);

        /*
         * In TYPO3 6.2, the `VariableFrontend` will serialize the value to set
         * in cache before it is send to the backend.
         */
        $secondArgument = (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<'))
            ? Argument::type('string')
            : $conditionProcessorMock;

        $transientMemoryProphecy->set($cacheIdentifier, $secondArgument, Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->will(function ($arguments) use ($transientMemoryProphecy) {
                $transientMemoryProphecy->has($arguments[0])
                    ->shouldBeCalled()
                    ->willReturn(true);

                $transientMemoryProphecy->get($arguments[0])
                    ->shouldBeCalled()
                    ->willReturn($arguments[1]);
            });

        $transientMemoryProphecy->setCache(Argument::type(AbstractFrontend::class))->shouldBeCalled();
        $cacheInstance = new VariableFrontend('ConditionProcessorFactoryTest', $transientMemoryProphecy->reveal());
        CacheService::get()->setCacheInstance($cacheInstance);

        // Actually executing the tests...
        $formObject = $this->getFormObject();

        $conditionProcessorFactoryMock1 = $this->getConditionProcessorFactoryMock($cacheIdentifier);
        $conditionProcessorFactoryMock1->expects($this->once())
            ->method('getNewProcessorInstance')
            ->willReturn($conditionProcessorMock);

        $conditionProcessorFactoryMock2 = $this->getConditionProcessorFactoryMock($cacheIdentifier);
        $conditionProcessorFactoryMock2->expects($this->never())
            ->method('getNewProcessorInstance');

        $conditionProcessorFactoryMock1->get($formObject);
        $conditionProcessorFactoryMock2->get($formObject);
    }

    /**
     * @param string $cacheIdentifier
     * @return \PHPUnit_Framework_MockObject_MockObject|ConditionProcessorFactory
     */
    protected function getConditionProcessorFactoryMock($cacheIdentifier)
    {
        /** @var ConditionProcessorFactory|\PHPUnit_Framework_MockObject_MockObject $conditionProcessorFactoryMock */
        $conditionProcessorFactoryMock = $this->getMockBuilder(ConditionProcessorFactory::class)
            ->setMethods(['getNewProcessorInstance', 'getCacheIdentifier'])
            ->getMock();

        $conditionProcessorFactoryMock->expects($this->atLeastOnce())
            ->method('getCacheIdentifier')
            ->willReturn($cacheIdentifier);

        return $conditionProcessorFactoryMock;
    }
}
