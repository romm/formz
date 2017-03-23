<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Tests\Fixture\Form\DefaultForm;
use Romm\Formz\Tests\Fixture\Form\ExtendedForm;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

trait AssetHandlerTestTrait
{

    /**
     * @param bool $extended
     * @return AssetHandlerFactory
     */
    protected function getAssetHandlerFactoryInstance($extended = false)
    {
        /** @var FormObject $formObject */
        $formObject = $extended
            ? $this->getExtendedFormObject()
            : $this->getDefaultFormObject();

        $formClassName = $extended
            ? ExtendedForm::class
            : DefaultForm::class;

        $formObject->setForm(new $formClassName());
        $formObject->setFormResult(new FormResult);

        $controllerContext = new ControllerContext();

        return AssetHandlerFactory::get($formObject, $controllerContext);
    }

    /**
     * Returns the same string, but without any space/tab/new line.
     *
     * @param string $string
     * @return string
     */
    protected function trimString($string)
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * Returns the string without multi-lines comments.
     *
     * @param string $string
     * @return string
     */
    protected function removeMultiLinesComments($string)
    {
        return preg_replace('#\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/#m', '', $string);
    }
}
