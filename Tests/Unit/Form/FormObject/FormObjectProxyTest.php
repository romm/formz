<?php
namespace Romm\Formz\Tests\Unit\Form\FormObject;

use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormObject\FormObjectProxy;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormObjectProxyTest extends AbstractUnitTest
{
    /**
     * The form instance passed to the proxy constructor can be retrieved with
     * its getter function.
     *
     * @test
     */
    public function formInstanceGettersReturnsForm()
    {
        $form = new DefaultForm;
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), $form);

        $this->assertSame($form, $formObjectProxy->getForm());
    }

    /**
     * Marking the form as submitted should work.
     *
     * @test
     */
    public function markFormAsSubmittedMarksFormAsSubmitted()
    {
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), new DefaultForm);

        $this->assertFalse($formObjectProxy->formWasSubmitted());
        $formObjectProxy->markFormAsSubmitted();
        $this->assertTrue($formObjectProxy->formWasSubmitted());
    }

    /**
     * Marking the form as validated should work.
     *
     * @test
     */
    public function markFormAsValidatedMarksFormAsValidated()
    {
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), new DefaultForm);

        $this->assertFalse($formObjectProxy->formWasValidated());
        $formObjectProxy->markFormAsValidated();
        $this->assertTrue($formObjectProxy->formWasValidated());
    }

    /**
     * The form result instance must always be the same.
     *
     * @test
     */
    public function formResultIsAlwaysTheSame()
    {
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), new DefaultForm);

        $formResult = $formObjectProxy->getFormResult();

        $this->assertInstanceOf(FormResult::class, $formResult);
        $this->assertSame($formResult, $formObjectProxy->getFormResult());
    }

    /**
     * The form hash should be calculated only once, and the same value should
     * be returned everytime.
     *
     * @test
     */
    public function formHashIsCalculatedOnce()
    {
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), new DefaultForm);

        $formHash = $formObjectProxy->getFormHash();
        $this->assertSame($formHash, $formObjectProxy->getFormHash());
    }

    /**
     * @test
     */
    public function setFormHashSetsFormHash()
    {
        $formObjectProxy = new FormObjectProxy($this->getDefaultFormObject(), new DefaultForm);

        $formHash = 'foo-bar';

        $formObjectProxy->setFormHash($formHash);
        $this->assertSame($formHash, $formObjectProxy->getFormHash());
    }
}
