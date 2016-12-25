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
class ClassViewHelper extends AbstractViewHelper
{
    const CLASS_ERRORS = 'errors';
    const CLASS_VALID = 'valid';

    /**
     * @var array
     */
    protected static $acceptedClassesNameSpace = [self::CLASS_ERRORS, self::CLASS_VALID];

    /**
     * @var Field
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $classValue;

    /**
     * @var string
     */
    protected $classNameSpace;

    /**
     * @var string
     */
    protected $className;

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
        $this->initializeClassNames();
        $this->initializeClassValue();
        $this->initializeFieldName();

        $result = 'formz-' . $this->classNameSpace . '-' . str_replace(' ', '-', $this->classValue);

        if ($this->service->formWasSubmitted()) {
            $propertyResult = $this->getRequestResultForProperty($this->fieldName);

            if (null !== $propertyResult) {
                switch ($this->classNameSpace) {
                    case self::CLASS_ERRORS:
                        if (true === $propertyResult->hasErrors()) {
                            $result .= ' ' . $this->classValue;
                        }
                        break;
                    case self::CLASS_VALID:
                        if (false === $propertyResult->hasErrors()) {
                            $result .= ' ' . $this->classValue;
                        }
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Will initialize the namespace and name of the class which is given as
     * argument to this ViewHelper.
     *
     * @throws \Exception
     */
    protected function initializeClassNames()
    {
        list($this->classNameSpace, $this->className) = GeneralUtility::trimExplode('.', $this->arguments['name']);

        if (false === in_array($this->classNameSpace, self::$acceptedClassesNameSpace)) {
            throw new \Exception(
                'The class "' . $this->arguments['name'] . '" is not valid: the namespace of the error must be one of the following: ' . implode(', ', self::$acceptedClassesNameSpace) . '.',
                1467623504
            );
        }
    }

    /**
     * Fetches the name of the field which should refer to this class. It can
     * either be a given value, or be empty if the ViewHelper is used inside a
     * `FieldViewHelper`.
     *
     * @throws \Exception
     */
    protected function initializeFieldName()
    {
        $this->fieldName = $this->arguments['field'];

        if (null === $this->fieldName
            && $this->service->fieldContextExists($this->renderingContext)
        ) {
            $this->fieldName = $this->service
                ->getCurrentField($this->renderingContext)
                ->getFieldName();
        }

        if (null === $this->fieldName) {
            throw new \Exception(
                'The field could not be fetched for the class "' . $this->arguments['name'] . '": please either use this view helper inside the view helper "' . FieldViewHelper::class . '", or fill the parameter "field" of this view helper with the field name you want.',
                1467623761
            );
        }
    }

    /**
     * Fetches the corresponding value of this class, which was defined in
     * TypoScript.
     *
     * @throws \Exception
     */
    protected function initializeClassValue()
    {
        /** @var FormViewHelper $form */
        $form = FormViewHelper::getVariable(FormViewHelper::FORM_VIEW_HELPER);
        $classesConfiguration = $form->getFormzConfiguration()->getView()->getClasses();

        /** @var ViewClass $class */
        $class = ObjectAccess::getProperty($classesConfiguration, $this->classNameSpace);

        if (false === $class->hasItem($this->className)) {
            throw new \Exception(
                'The class "' . $this->arguments['name'] . '" is not valid: the class name "' . $this->className . '" was not found in the namespace "' . $this->classNameSpace . '".',
                1467623662
            );
        }

        $this->classValue = $class->getItem($this->className);
    }

    /**
     * Returns the result for the given property only if the current request has
     * a result for the form.
     *
     * @param string $property
     * @return Result|null
     */
    protected function getRequestResultForProperty($property)
    {
        /** @var Result $requestResult */
        $requestResult = FormViewHelper::getVariable(FormViewHelper::FORM_RESULT);

        return (false !== $requestResult)
            ? $requestResult->forProperty($property)
            : null;
    }
}
