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

/**
 * Transforms a value in uppercase.
 *
 * @see \Romm\Formz\Behaviours\AbstractBehaviour
 */
class ToUpperCaseBehaviour extends AbstractBehaviour
{

    /**
     * @inheritdoc
     */
    public function applyBehaviour($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->applyBehaviour($val);
            }
        } else {
            $value = $this->applyBehaviourInternal($value);
        }

        return $value;
    }

    /**
     * Transforms the given value in upper case.
     *
     * @param    mixed $value The value.
     * @return    string
     */
    protected function applyBehaviourInternal($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
