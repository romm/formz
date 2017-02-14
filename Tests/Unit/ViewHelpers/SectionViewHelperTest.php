<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\SectionViewHelper;
use Romm\Formz\ViewHelpers\Service\FieldService;
use Romm\Formz\ViewHelpers\Service\SectionService;

class SectionViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FieldService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldService::class)
            ->setMethods(['checkIsInsideFieldViewHelper'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('checkIsInsideFieldViewHelper');

        /** @var SectionService|\PHPUnit_Framework_MockObject_MockObject $sectionService */
        $sectionService = $this->getMockBuilder(SectionService::class)
            ->setMethods(['addSectionClosure'])
            ->getMock();
        $sectionService->expects($this->once())
            ->method('addSectionClosure');

        UnitTestContainer::get()->registerMockedInstance(SectionService::class, $sectionService);

        $viewHelper = new SectionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->initializeArguments();

        $viewHelper->render();
    }
}
