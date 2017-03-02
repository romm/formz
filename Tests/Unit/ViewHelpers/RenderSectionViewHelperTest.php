<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Service\ViewHelper\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\SectionViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\RenderSectionViewHelper;

class RenderSectionViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['fieldContextExists'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('fieldContextExists')
            ->willReturn(true);

        /** @var SectionViewHelperService|\PHPUnit_Framework_MockObject_MockObject $sectionService */
        $sectionService = $this->getMockBuilder(SectionViewHelperService::class)
            ->setMethods(['getSectionClosure'])
            ->getMock();
        $sectionService->expects($this->once())
            ->method('getSectionClosure')
            ->willReturn(function () {
                return 'foo';
            });

        UnitTestContainer::get()->registerMockedInstance(SectionViewHelperService::class, $sectionService);

        $viewHelper = new RenderSectionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->initializeArguments();

        $this->assertEquals(
            'foo',
            $viewHelper->render()
        );
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $viewHelper = new RenderSectionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldViewHelperService);
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
