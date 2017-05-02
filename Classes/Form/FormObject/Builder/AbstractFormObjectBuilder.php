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

use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\FormDefinition;
use Romm\Formz\Form\FormObject\Definition\FormDefinitionObject;
use Romm\Formz\Form\FormObject\FormObjectStatic;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFormObjectBuilder implements FormObjectBuilderInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var FormObjectStatic
     */
    protected $static;

    /**
     * @param string $className
     * @return FormObjectStatic
     */
    public function getStaticInstance($className)
    {
        $this->className = $className;

        $formDefinition = $this->getFormDefinition();

        /** @var FormDefinitionObject $formDefinitionObject */
        $formDefinitionObject = GeneralUtility::makeInstance(FormDefinitionObject::class, $formDefinition);

        $this->static = Core::instantiate(
            FormObjectStatic::class,
            $this->className,
            $formDefinitionObject
        );

        $this->process();

        $formDefinitionObject->getDefinition();

        return $this->static;
    }

    /**
     * Override this function in child classes to implement custom processes.
     */
    protected function process()
    {
    }

    /**
     * Must return the form definition of the form class.
     *
     * @return FormDefinition
     */
    abstract public function getFormDefinition();
}
