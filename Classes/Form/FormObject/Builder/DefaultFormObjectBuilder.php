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

namespace Romm\Formz\Form\FormObject\Builder;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Default form object builder.
 *
 * It fetches the form configuration array from TypoScript, and automatically
 * inserts the class properties of the form in the properties of the form
 * object.
 */
class DefaultFormObjectBuilder extends AbstractFormObjectBuilder
{
    const FORM_DEFINITION_BUILT = 'formDefinitionBuilt';

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @see DefaultFormObjectBuilder
     */
    public function process()
    {
        $this->insertObjectProperties();
    }

    /**
     * @return FormDefinitionObject
     */
    protected function getFormDefinitionObject()
    {
        $configurationArray = TypoScriptService::get()->getFormConfiguration($this->className);
        $configurationObject = ConfigurationObjectFactory::convert(FormDefinition::class, $configurationArray);

        /** @var FormDefinitionObject $formDefinitionObject */
        $formDefinitionObject = GeneralUtility::makeInstance(
            FormDefinitionObject::class,
            $configurationObject->getObject(true),
            $configurationObject->getValidationResult()
        );

        $this->signalSlotDispatcher->dispatch(
            self::class,
            self::FORM_DEFINITION_BUILT,
            [$this->className, $formDefinitionObject]
        );

        return $formDefinitionObject;
    }

    /**
     * Will insert all the accessible properties of the form class in the form
     * object.
     */
    protected function insertObjectProperties()
    {
        foreach ($this->static->getProperties() as $propertyName) {
            if (false === $this->static->getDefinition()->hasField($propertyName)) {
                $this->static->getDefinition()->addField($propertyName);
            }
        }
    }

    /**
     * @return FormDefinition
     */
    public function getFormDefinition()
    {
        return null;
    }

    /**
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }
}
