<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers\Slot;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Legacy\Slot\OldHasViewHelper;
use Romm\Formz\Service\ViewHelper\SlotViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\Tests\Unit\ViewHelpers\AbstractViewHelperUnitTest;
use Romm\Formz\ViewHelpers\Slot\HasViewHelper;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class HasViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderThenViewHelper()
    {
        $slotArgument = 'foo-slot';

        /** @var HasViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder($this->getSlotHasViewHelperClassName())
            ->setMethods(['renderThenChild', 'renderElseChild'])
            ->getMock();

        $viewHelper->expects($this->once())
            ->method('renderThenChild');
        $viewHelper->expects($this->never())
            ->method('renderElseChild');

        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['getSlotClosure', 'hasSlotClosure'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlotClosure')
            ->with($slotArgument)
            ->willReturn(true);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments(['slot' => $slotArgument]);

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $viewHelper->injectReflectionService(new ReflectionService);
        }

        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function renderElseViewHelper()
    {
        $slotArgument = 'foo-slot';

        /** @var HasViewHelper|\PHPUnit_Framework_MockObject_MockObject $viewHelper */
        $viewHelper = $this->getMockBuilder($this->getSlotHasViewHelperClassName())
            ->setMethods(['renderThenChild', 'renderElseChild'])
            ->getMock();

        $viewHelper->expects($this->never())
            ->method('renderThenChild');
        $viewHelper->expects($this->once())
            ->method('renderElseChild');

        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SlotViewHelperService|\PHPUnit_Framework_MockObject_MockObject $slotService */
        $slotService = $this->getMockBuilder(SlotViewHelperService::class)
            ->setMethods(['getSlotClosure', 'hasSlotClosure'])
            ->getMock();
        $slotService->expects($this->once())
            ->method('hasSlotClosure')
            ->with($slotArgument)
            ->willReturn(false);

        UnitTestContainer::get()->registerMockedInstance(SlotViewHelperService::class, $slotService);

        $viewHelper->setRenderingContext($this->renderingContext);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->setArguments(['slot' => $slotArgument]);

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $viewHelper->injectReflectionService(new ReflectionService);
        }

        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $this->setExpectedException(ContextNotFoundException::class);

        $className = $this->getSlotHasViewHelperClassName();
        /** @var HasViewHelper $viewHelper */
        $viewHelper = new $className;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldViewHelperService);

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $viewHelper->injectReflectionService(new ReflectionService);
        }

        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @return string
     */
    protected function getSlotHasViewHelperClassName()
    {
        return (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.3.0', '<'))
            ? OldHasViewHelper::class
            : HasViewHelper::class;
    }
}
