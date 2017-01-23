<?php
namespace Romm\Formz\Tests\Unit\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\Tests\Unit\UnitTestContainer;
use Romm\Formz\ViewHelpers\OptionViewHelper;
use Romm\Formz\ViewHelpers\Service\FormzViewHelperService;

class OptionViewHelperTest extends AbstractViewHelperUnitTest
{
    /**
     * @test
     */
    public function renderViewHelper()
    {
        /** @var FormzViewHelperService|\PHPUnit_Framework_MockObject_MockObject $formzViewHelperService */
        $formzViewHelperService = $this->getMock(FormzViewHelperService::class, ['setFieldOption']);
        $formzViewHelperService->expects($this->once())
            ->method('setFieldOption')
            ->with('foo', 'bar');

        $formzViewHelperService->setCurrentField(new Field);

        UnitTestContainer::get()->registerMockedInstance(FormzViewHelperService::class, $formzViewHelperService);

        $viewHelper = new OptionViewHelper;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->injectFormzViewHelperService($formzViewHelperService);
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
        $viewHelper->injectFormzViewHelperService(new FormzViewHelperService);
        $viewHelper->initializeArguments();

        $this->setExpectedException(ContextNotFoundException::class);

        $viewHelper->render();
    }
}
