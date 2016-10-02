<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\View\Classes\ViewClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This view helper is used to manage the properties set in TypoScript at the
 * path `config.tx_formz.view.classes`.
 *
 * Two groups of classes are handled: `errors` and `valid`. The classes will be
 * "activated" only when the field has been validated, and the result matches
 * the class group.
 *
 * In the option `name`, you must indicate which class from which group you want
 * to manage, for example `errors.has-error` for the class `has-error` from the
 * group `errors`.
 *
 * If the field currently being rendered with Fluid is not using the view helper
 * `FieldViewHelper` (all its skeleton is written manually), you may have to use
 * the option `field`, which should then contain the name of the field.
 *
 * Please be aware that this view helper is useful only when used at the same
 * level or under the HTML element containing the field selector (usually the
 * one with the data attribute `formz-field-container`). You may encounter
 * strange behaviours if you do not respect this requirement.
 */
class ClassViewHelper extends AbstractViewHelper implements CompilableInterface
{

    const CLASS_ERRORS = 'errors';
    const CLASS_VALID = 'valid';

    /**
     * @var array
     */
    protected static $acceptedClassesNameSpace = [self::CLASS_ERRORS, self::CLASS_VALID];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the class which will be managed.', true);
        $this->registerArgument('field', 'string', 'Name of the field which will be managed. By default, it is the field from the current `FieldViewHelper`.');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * See class description.
     *
     * @inheritdoc
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();

        if (null === FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER)) {
            throw new \Exception(
                'The view helper "' . self::class . '" must be used inside the view helper "' . FormViewHelper::class . '".',
                1467623374
            );
        }

        list($classNameSpace, $className) = GeneralUtility::trimExplode('.', $arguments['name']);

        if (false === in_array($classNameSpace, self::$acceptedClassesNameSpace)) {
            throw new \Exception(
                'The class "' . $arguments['name'] . '" is not valid: the namespace of the error must be one of the following: ' . implode(', ', self::$acceptedClassesNameSpace) . '.',
                1467623504
            );
        }

        /** @var FormViewHelper $form */
        $form = FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER);
        $classesConfiguration = $form->getFormzConfiguration()->getView()->getClasses();

        /** @var ViewClass $class */
        $class = ObjectAccess::getProperty($classesConfiguration, $classNameSpace);

        if (false === $class->hasItem($className)) {
            throw new \Exception(
                'The class "' . $arguments['name'] . '" is not valid: the class name "' . $className . '" was not found in the namespace "' . $classNameSpace . '".',
                1467623662
            );
        }

        /** @var Field $field */
        $field = $arguments['field'];
        if (null === $field
            && $viewHelperVariableContainer->exists(FieldViewHelper::class, FieldViewHelper::FIELD_INSTANCE)
        ) {
            $field = $viewHelperVariableContainer->get(FieldViewHelper::class, FieldViewHelper::FIELD_INSTANCE);
        }

        if (null === $field) {
            throw new \Exception(
                'The field could not be fetched for the class "' . $arguments['name'] . '": please either use this view helper inside the view helper "' . FieldViewHelper::class . '", or fill the parameter "field" of this view helper with the field name you want.',
                1467623761
            );
        }

        $classValue = $class->getItem($className);
        $result = 'formz-' . $classNameSpace . '-' . str_replace(' ', '-', $classValue);

        if (true === FormViewHelper::getVariable(FormViewHelper::FORM_WAS_SUBMITTED)) {
            $propertyResult = self::getRequestResultForProperty($field->getFieldName());

            if (null !== $propertyResult) {
                switch ($classNameSpace) {
                    case self::CLASS_ERRORS:
                        if (true === $propertyResult->hasErrors()) {
                            $result .= ' ' . $classValue;
                        }
                        break;
                    case self::CLASS_VALID:
                        if (false === $propertyResult->hasErrors()) {
                            $result .= ' ' . $classValue;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the result for the given property only if the current request has
     * a result for the form.
     *
     * @param string $property
     * @return Result|null
     */
    protected static function getRequestResultForProperty($property)
    {
        /** @var Result $requestResult */
        $requestResult = FormViewHelper::getVariable(FormViewHelper::FORM_RESULT);

        return (false !== $requestResult)
            ? $requestResult->forProperty($property)
            : null;
    }
}
