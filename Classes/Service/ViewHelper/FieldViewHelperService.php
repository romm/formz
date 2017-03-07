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

namespace Romm\Formz\Service\ViewHelper;

use Romm\Formz\Configuration\Form\Field\Field;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Contains methods to help view helpers to manipulate data concerning the
 * current field.
 */
class FieldViewHelperService implements SingletonInterface
{
    /**
     * @var Field
     */
    protected $currentField;

    /**
     * @var array
     */
    protected $fieldOptions = [];

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->currentField = null;
        $this->fieldOptions = [];
    }

    /**
     * Checks that the `FieldViewHelper` has been called. If not, an exception
     * is thrown.
     *
     * @return bool
     */
    public function fieldContextExists()
    {
        return $this->currentField instanceof Field;
    }

    /**
     * Returns the current field which was defined by the `FieldViewHelper`.
     *
     * Returns null if no current field was found.
     *
     * @return Field|null
     */
    public function getCurrentField()
    {
        return $this->currentField;
    }

    /**
     * @param Field $field
     */
    public function setCurrentField(Field $field)
    {
        $this->currentField = $field;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setFieldOption($name, $value)
    {
        $this->fieldOptions[$name] = $value;
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->fieldOptions;
    }
}
