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

namespace Romm\Formz\Form\Definition;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\ConfigurationState;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;

abstract class AbstractFormDefinitionComponent
{
    use MagicMethodsTrait {
        handlePropertyMagicMethod as handlePropertyMagicMethodInternal;
    }
    use ParentsTrait {
        attachParent as private attachParentInternal;
        attachParents as private attachParentsInternal;
    }

    /**
     * @var bool
     */
    private $parentsAttached = false;

    /**
     * This method is used by setter methods, and other methods which goal is to
     * modify a property value.
     *
     * It checks that the definition is not frozen, and if it is actually frozen
     * an exception is thrown.
     *
     * @throws PropertyNotAccessibleException
     */
    protected function checkDefinitionFreezeState()
    {
        if ($this->isDefinitionFrozen()) {
            $methodName = debug_backtrace()[1]['function'];

            throw PropertyNotAccessibleException::formDefinitionFrozenMethod(get_class($this), $methodName);
        }
    }

    /**
     * @return bool
     */
    protected function isDefinitionFrozen()
    {
        return $this->getState()
            && $this->getState()->isFrozen();
    }

    /**
     * @return ConfigurationState
     */
    protected function getState()
    {
        if ($this->hasParent(FormDefinition::class)) {
            return $this->getFirstParent(FormDefinition::class)->getState();
        } elseif ($this->hasParent(Configuration::class)) {
            return $this->getFirstParent(Configuration::class)->getState();
        }

        return null;
    }

    /**
     * Overrides the magic methods handling from the Configuration Object API.
     *
     * Blocks the parents feature once it has been used.
     *
     * @param object[] $parents
     */
    public function attachParents(array $parents)
    {
        if (false === $this->parentsAttached) {
            $this->attachParentsInternal($parents);
            $this->parentsAttached = true;
        }
    }

    /**
     * @see attachParents()
     *
     * @param object $parent
     * @param bool   $direct
     */
    public function attachParent($parent, $direct = true)
    {
        $this->checkDefinitionFreezeState();

        if (false === $this->parentsAttached) {
            $this->attachParentInternal($parent, $direct);
        }
    }

    /**
     * Overrides the magic methods handling from the Configuration Object API: a
     * magic setter method must be accessible only for this API, otherwise an
     * exception must be thrown.
     *
     * @param string $property
     * @param string $type
     * @param array  $arguments
     * @return mixed
     * @throws PropertyNotAccessibleException
     */
    protected function handlePropertyMagicMethod($property, $type, array $arguments)
    {
        if ($type === 'set'
            && $this->isPropertyAccessible($property)
            && false === ConfigurationObjectFactory::getInstance()->isRunning()
        ) {
            throw PropertyNotAccessibleException::formDefinitionFrozenProperty(get_class($this), $property);
        }

        return $this->handlePropertyMagicMethodInternal($property, $type, $arguments);
    }

    /**
     * @param array  $data
     * @param string $property
     * @param string $name
     */
    protected static function forceNameForProperty(&$data, $property, $name = 'name')
    {
        if (isset($data[$property])
            && is_array($data[$property])
        ) {
            foreach ($data[$property] as $key => $entry) {
                $data[$property][$key][$name] = $key;
            }
        }
    }
}
