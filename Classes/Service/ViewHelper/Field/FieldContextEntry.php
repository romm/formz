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

namespace Romm\Formz\Service\ViewHelper\Field;

use Romm\Formz\Form\Definition\Field\Field;

class FieldContextEntry
{
    /**
     * @var Field
     */
    protected $field;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return Field|null
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
