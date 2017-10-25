<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\Configuration\View\Classes\ViewClass;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidEntryException;
use Romm\Formz\Exceptions\UnregisteredConfigurationException;
use Romm\Formz\Service\ViewHelper\Field\FieldViewHelperService;
use Romm\Formz\Service\ViewHelper\Form\FormViewHelperService;
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
 * one with the data attribute `fz-field-container`). You may encounter
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
     * @var FormViewHelperService
     */
    protected $formService;

    /**
     * @var FieldViewHelperService
     */
    protected $fieldService;

    /**
     * @var string
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
        parent::initializeArguments();

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

        $result = vsprintf(
            'fz-%s-%s',
            [
                $this->classNameSpace,
                str_replace(' ', '-', $this->classValue)
            ]
        );

        $result .= $this->getFormResultClass();

        return $result;
    }

    /**
     * Will initialize the namespace and name of the class which is given as
     * argument to this ViewHelper.
     *
     * @throws InvalidEntryException
     */
    protected function initializeClassNames()
    {
        list($this->classNameSpace, $this->className) = GeneralUtility::trimExplode('.', $this->arguments['name']);

        if (false === in_array($this->classNameSpace, self::$acceptedClassesNameSpace)) {
            throw InvalidEntryException::invalidCssClassNamespace($this->arguments['name'], self::$acceptedClassesNameSpace);
        }
    }

    /**
     * Fetches the name of the field which should refer to this class. It can
     * either be a given value, or be empty if the ViewHelper is used inside a
     * `FieldViewHelper`.
     *
     * @throws EntryNotFoundException
     */
    protected function initializeFieldName()
    {
        $this->fieldName = $this->arguments['field'];

        if (empty($this->fieldName)
            && $this->fieldService->fieldContextExists()
        ) {
            $this->fieldName = $this->fieldService
                ->getCurrentField()
                ->getName();
        }

        if (null === $this->fieldName) {
            throw EntryNotFoundException::classViewHelperFieldNotFound($this->arguments['name']);
        }
    }

    /**
     * Fetches the corresponding value of this class, which was defined in
     * TypoScript.
     *
     * @throws UnregisteredConfigurationException
     */
    protected function initializeClassValue()
    {
        $classesConfiguration = $this->formService
            ->getFormObject()
            ->getConfiguration()
            ->getRootConfiguration()
            ->getView()
            ->getClasses();

        /** @var ViewClass $class */
        $class = ObjectAccess::getProperty($classesConfiguration, $this->classNameSpace);

        if (false === $class->hasItem($this->className)) {
            throw UnregisteredConfigurationException::cssClassNameNotFound($this->arguments['name'], $this->classNameSpace, $this->className);
        }

        $this->classValue = $class->getItem($this->className);
    }

    /**
     * Checks if the form was submitted, then parses its result to handle
     * classes depending on TypoScript configuration.
     *
     * @return string
     */
    protected function getFormResultClass()
    {
        $result = '';
        $formObject = $this->formService->getFormObject();

        if ($formObject->formWasSubmitted()
            && $formObject->hasFormResult()
        ) {
            $fieldResult = $formObject->getFormResult()->forProperty($this->fieldName);
            $result = $this->getPropertyResultClass($fieldResult);
        }

        return $result;
    }

    /**
     * @param Result $propertyResult
     * @return string
     */
    protected function getPropertyResultClass(Result $propertyResult)
    {
        $result = '';

        switch ($this->classNameSpace) {
            case self::CLASS_ERRORS:
                $result = $this->getPropertyErrorClass($propertyResult);
                break;
            case self::CLASS_VALID:
                $result = $this->getPropertyValidClass($propertyResult);
                break;
        }

        return $result;
    }

    /**
     * @param Result $propertyResult
     * @return string
     */
    protected function getPropertyErrorClass(Result $propertyResult)
    {
        return (true === $propertyResult->hasErrors())
            ? ' ' . $this->classValue
            : '';
    }

    /**
     * @param Result $propertyResult
     * @return string
     */
    protected function getPropertyValidClass(Result $propertyResult)
    {
        $result = '';
        $formObject = $this->formService->getFormObject();
        $field = $formObject->getConfiguration()->getField($this->fieldName);

        if ($formObject->hasForm()
            && false === $propertyResult->hasErrors()
            && false === $formObject->getFormResult()->fieldIsDeactivated($field)
        ) {
            $fieldValue = ObjectAccess::getProperty($formObject->getForm(), $this->fieldName);

            if (false === empty($fieldValue)) {
                $result .= ' ' . $this->classValue;
            }
        }

        return $result;
    }

    /**
     * @param FormViewHelperService $service
     */
    public function injectFormService(FormViewHelperService $service)
    {
        $this->formService = $service;
    }

    /**
     * @param FieldViewHelperService $service
     */
    public function injectFieldService(FieldViewHelperService $service)
    {
        $this->fieldService = $service;
    }
}
