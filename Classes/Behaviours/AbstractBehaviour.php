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

namespace Romm\Formz\Behaviours;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Each custom behaviour must inherit this class and fill the `applyBehaviour()`
 * function.
 */
abstract class AbstractBehaviour implements BehaviourInterface, SingletonInterface
{
}
