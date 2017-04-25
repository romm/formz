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

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\Field\Field;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
     * @var StandaloneView[]
     */
    protected $view;

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

    /**
     * Returns a view instance, based on the template file of the layout. The
     * view is stored in local cache, to improve performance: the template file
     * content will be fetched only once.
     *
     * @param Layout $layout
     * @return StandaloneView
     */
    public function getView(Layout $layout)
    {
        $identifier = $layout->getTemplateFile();

        if (null === $this->view[$identifier]) {
            /** @var StandaloneView $view */
            $view = Core::instantiate(StandaloneView::class);
            $view->setTemplatePathAndFilename($layout->getTemplateFile());

            $this->view[$identifier] = $view;
        }

        return $this->view[$identifier];
    }
}
