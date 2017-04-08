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

namespace Romm\Formz\Service\ViewHelper;

use Closure;
use Romm\Formz\Exceptions\EntryNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

class SlotViewHelperService implements SingletonInterface
{
    /**
     * Contains the closures which will render the registered slots. The keys
     * of this array are the names of the slots.
     *
     * @var Closure[]
     */
    private $closures = [];

    /**
     * @var array[]
     */
    private $arguments = [];

    /**
     * @var RenderingContextInterface|\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface[]
     */
    private $renderingContext = [];

    /**
     * @var array[]
     */
    private $injectedVariables = [];

    /**
     * @var array[]
     */
    private $savedVariables = [];

    /**
     * Adds a closure - which will render the slot with the given name - to the
     * private storage in this class.
     *
     * @param string                    $name
     * @param Closure                   $closure
     * @param array                     $arguments
     * @param RenderingContextInterface $renderingContext
     */
    public function addSlot($name, Closure $closure, array $arguments, RenderingContextInterface $renderingContext)
    {
        $this->closures[$name] = $closure;
        $this->arguments[$name] = $arguments;
        $this->renderingContext[$name] = $renderingContext;
    }

    /**
     * Returns the closure which will render the slot with the given name.
     *
     * @param string $name
     * @return Closure
     * @throws EntryNotFoundException
     */
    public function getSlotClosure($name)
    {
        if (false === $this->hasSlot($name)) {
            throw EntryNotFoundException::slotClosureSlotNotFound($name);
        }

        return $this->closures[$name];
    }

    /**
     * Returns the closure which will render the slot with the given name.
     *
     * @param string $name
     * @return array
     * @throws EntryNotFoundException
     */
    public function getSlotArguments($name)
    {
        if (false === $this->hasSlot($name)) {
            throw EntryNotFoundException::slotArgumentsSlotNotFound($name);
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasSlot($name)
    {
        return true === isset($this->closures[$name]);
    }

    /**
     * Will merge the given arguments with the ones registered by the given
     * slot, and inject them in the template variable container.
     *
     * Note that the variables that are already defined are first saved before
     * being overridden, so they can be restored later.
     *
     * @param string                    $slotName
     * @param array                     $arguments
     */
    public function addTemplateVariables($slotName, array $arguments)
    {
        $templateVariableContainer = $this->getTemplateVariableContainer($slotName);
        $savedArguments = [];

        ArrayUtility::mergeRecursiveWithOverrule(
            $arguments,
            $this->getSlotArguments($slotName)
        );

        foreach ($arguments as $key => $value) {
            if ($templateVariableContainer->exists($key)) {
                $savedArguments[$key] = $templateVariableContainer->get($key);
                $templateVariableContainer->remove($key);
            }

            $templateVariableContainer->add($key, $value);
        }

        $this->injectedVariables[$slotName] = $arguments;
        $this->savedVariables[$slotName] = $savedArguments;
    }

    /**
     * Will remove all variables previously injected in the template variable
     * container, and restore the ones that were saved before being overridden.
     *
     * @param string                    $slotName
     */
    public function restoreTemplateVariables($slotName)
    {
        $templateVariableContainer = $this->getTemplateVariableContainer($slotName);
        $mergedArguments = (isset($this->injectedVariables[$slotName])) ? $this->injectedVariables[$slotName] : [];
        $savedArguments = (isset($this->savedVariables[$slotName])) ? $this->savedVariables[$slotName] : [];

        foreach (array_keys($mergedArguments) as $key) {
            $templateVariableContainer->remove($key);
        }

        foreach ($savedArguments as $key => $value) {
            $templateVariableContainer->add($key, $value);
        }
    }

    /**
     * @param string $slotName
     * @return TemplateVariableContainer
     */
    protected function getTemplateVariableContainer($slotName)
    {
        /** @var TemplateVariableContainer $templateVariableContainer */
        $templateVariableContainer = $this->renderingContext[$slotName]->getTemplateVariableContainer();

        return $templateVariableContainer;
    }

    /**
     * Resets the service variables.
     */
    public function resetState()
    {
        $this->closures = [];
        $this->arguments = [];
        $this->injectedVariables = [];
        $this->savedVariables = [];
    }
}
