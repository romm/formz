<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\OptionViewHelper;

class OptionViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FieldViewHelperService|\PHPUnit_Framework_MockObject_MockObject $fieldService */
        $fieldService = $this->getMockBuilder(FieldViewHelperService::class)
            ->setMethods(['setFieldOption'])
            ->getMock();
        $fieldService->expects($this->once())
            ->method('setFieldOption')
            ->with('foo', 'bar');

        $fieldService->setCurrentField(new Field('foo'));

        UnitTestContainer::get()->registerMockedInstance(FieldViewHelperService::class, $fieldService);

        $viewHelper = new OptionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->initializeArguments();
        $viewHelper->setArguments([
            'name'  => 'foo',
            'value' => 'bar'
        ]);

        $viewHelper->render();

        UnitTestContainer::get()->resetInstances();
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
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
