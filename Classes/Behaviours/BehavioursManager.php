<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Behaviours;

use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Behaviours are used to apply custom modifications on a field, just before
 * its validation.
 *
 * For example, you may want to trim a field value when the form is submitted:
 * you can do it using behaviours.
 */
class BehavioursManager implements SingletonInterface
{

    /**
     * This function will loop on all the fields of the form configuration, and
     * for each one check if it uses behaviours. If it does, and if it is found
     * in `$formProperties`, the behaviours are applied and the value is
     * changed.
     *
     * @param array $formProperties    Properties values of the submitted form.
     * @param Form  $formConfiguration Configuration object of the form.
     * @return array
     */
    public function applyBehaviourOnPropertiesArray(array $formProperties, Form $formConfiguration)
    {
        foreach ($formConfiguration->getFields() as $fieldName => $field) {
            if (true === isset($formProperties[$fieldName])) {
                foreach ($field->getBehaviours() as $behaviour) {
                    /** @var AbstractBehaviour $behaviourInstance */
                    $behaviourInstance = GeneralUtility::makeInstance($behaviour->getClassName());

                    // Applying the behaviour on the field's value.
                    $formProperties[$fieldName] = $behaviourInstance->applyBehaviour($formProperties[$fieldName]);
                }
            }
        }

        return $formProperties;
    }

    /**
     * This is the same function as `applyBehaviourOnPropertiesArray`, but works
     * with an actual form object instance.
     *
     * @param object     $formInstance
     * @param FormObject $formObject
     */
    public function applyBehaviourOnFormInstance($formInstance, FormObject $formObject)
    {
        if (is_object($formInstance)) {
            foreach ($formObject->getConfiguration()->getFields() as $fieldName => $field) {
                if (ObjectAccess::isPropertyGettable($formInstance, $fieldName)
                    && ObjectAccess::isPropertySettable($formInstance, $fieldName)
                ) {
                    $propertyValue = ObjectAccess::getProperty($formInstance, $fieldName);

                    foreach ($field->getBehaviours() as $behaviour) {
                        /** @var AbstractBehaviour $behaviourInstance */
                        $behaviourInstance = GeneralUtility::makeInstance($behaviour->getClassName());

                        // Applying the behaviour on the field's value.
                        $propertyValue = $behaviourInstance->applyBehaviour($propertyValue);
                    }

                    ObjectAccess::setProperty($formInstance, $fieldName, $propertyValue);
                }
            }
        }
    }
}
