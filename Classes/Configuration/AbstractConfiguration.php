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

namespace Romm\Formz\Configuration;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use Romm\Formz\Exceptions\PropertyNotAccessibleException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractConfiguration
{
    use MagicMethodsTrait {
        handlePropertyMagicMethod as handlePropertyMagicMethodInternal;
    }
    use ParentsTrait {
        attachParent as private attachParentInternal;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath($path)
    {
        return GeneralUtility::getFileAbsFileName($path);
    }

    /**
     * This method is used by setter methods, and other methods which goal is to
     * modify a property value.
     *
     * It checks that the definition is not frozen, and if it is actually frozen
     * an exception is thrown.
     *
     * @throws PropertyNotAccessibleException
     */
    protected function checkConfigurationFreezeState()
    {
        if ($this->isConfigurationFrozen()) {
            $methodName = debug_backtrace()[1]['function'];

            throw PropertyNotAccessibleException::rootConfigurationFrozenMethod(get_class($this), $methodName);
        }
    }

    /**
     * @return bool
     */
    protected function isConfigurationFrozen()
    {
        return $this->getState()
            && $this->getState()->isFrozen();
    }

    /**
     * @return ConfigurationState
     */
    protected function getState()
    {
        if ($this->hasParent(Configuration::class)) {
            return $this->getFirstParent(Configuration::class)->getState();
        }

        return null;
    }

    /**
     * @param object $parent
     * @param bool   $direct
     */
    public function attachParent($parent, $direct = true)
    {
        $this->checkConfigurationFreezeState();
        $this->attachParentInternal($parent, $direct);
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
            throw PropertyNotAccessibleException::rootConfigurationFrozenProperty(get_class($this), $property);
        }

        return $this->handlePropertyMagicMethodInternal($property, $type, $arguments);
    }
}
