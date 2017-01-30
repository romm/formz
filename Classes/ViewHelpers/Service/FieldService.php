<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\ViewHelpers\Service;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ContextNotFoundException;
use Romm\Formz\ViewHelpers\FieldViewHelper;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Contains methods to help view helpers to manipulate data concerning the
 * current field.
 */
class FieldService implements SingletonInterface
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
     * Unique instance of view, stored to save some performance.
     *
     * @var StandaloneView
     */
    protected $view;

    /**
     * Reset every state that can be used by this service.
     */
    public function resetState()
    {
        $this->currentField = null;
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

    /**
     * @return $this
     */
    public function resetFieldOptions()
    {
        $this->fieldOptions = [];

        return $this;
    }

    /**
     * Unset the current field.
     *
     * @return $this
     */
    public function removeCurrentField()
    {
        $this->currentField = null;

        return $this;
    }

    /**
     * Checks that the `FieldViewHelper` has been called. If not, an exception
     * is thrown.
     *
     * @throws \Exception
     */
    public function checkIsInsideFieldViewHelper()
    {
        if (false === $this->fieldContextExists()) {
            throw new ContextNotFoundException(
                'The view helper "' . get_called_class() . '" must be used inside the view helper "' . FieldViewHelper::class . '".',
                1465243085
            );
        }
    }

    /**
     * @return StandaloneView
     */
    public function getView()
    {
        if (null === $this->view) {
            $this->view = Core::instantiate(StandaloneView::class);
        }

        return $this->view;
    }
}
