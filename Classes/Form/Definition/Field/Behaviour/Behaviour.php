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

namespace Romm\Formz\Form\Definition\Field\Behaviour;

use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;

class Behaviour extends AbstractFormDefinitionComponent
{
    /**
     * @var string
     * @validate NotEmpty
     */
    private $name;

    /**
     * @var string
     * @validate NotEmpty
     * @validate Romm.ConfigurationObject:ClassImplements(interface=Romm\Formz\Behaviours\BehaviourInterface)
     */
    private $className;

    /**
     * @param string $name
     * @param string $className
     */
    public function __construct($name, $className)
    {
        $this->name = $name;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
