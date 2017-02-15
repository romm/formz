<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\OptionViewHelper;
use Romm\Formz\ViewHelpers\Service\FieldService;

class OptionViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FieldService|\PHPUnit_Framework_MockObject_MockObject $formService */
        $formService = $this->getMockBuilder(FieldService::class)
            ->setMethods(['setFieldOption'])
            ->getMock();
        $formService->expects($this->once())
            ->method('setFieldOption')
            ->with('foo', 'bar');

        $fieldService = new FieldService;
        $fieldService->setCurrentField(new Field);

        UnitTestContainer::get()->registerMockedInstance(FieldService::class, $formService);

        $viewHelper = new OptionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService($fieldService);
        $viewHelper->initializeArguments();
        $viewHelper->setArguments([
            'name'  => 'foo',
            'value' => 'bar'
        ]);

        $viewHelper->render();
    }

    /**
     * This ViewHelper must be used from inside a `FieldViewHelper`.
     *
     * @test
     */
    public function renderViewHelperWithoutFieldThrowsException()
    {
        $viewHelper = new OptionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFieldService(new FieldService);
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
