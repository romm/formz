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

use Romm\Formz\Configuration\View\Layouts\Layout;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\Definition\Field\Field;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Contains methods to help view helpers to manipulate data concerning the
 * current field.
 */
class FieldViewHelperService implements SingletonInterface
{
    /**
     * Contains all current fields being rendered by FormZ: if a field is
     * rendered beneath another field, several entries will be added to this
     * property.
     *
     * @var FieldContextEntry[]
     */
    protected $contextEntries = [];

    /**
     * @var StandaloneView[]
     */
    protected $view;

    /**
     * Adds a new context entry to the entries array. The other field-related
     * methods will be processed on this entry until the field rendering has
     * ended.
     *
     * @param Field $field
     */
    public function setCurrentField(Field $field)
    {
        $this->contextEntries[] = GeneralUtility::makeInstance(FieldContextEntry::class, $field);
    }

    /**
     * Removes the current field context entry.
     */
    public function removeCurrentField()
    {
        array_pop($this->contextEntries);
    }

    /**
     * Checks that a field context is found.
     *
     * @return bool
     */
    public function fieldContextExists()
    {
        return false === empty($this->contextEntries);
    }

    /**
     * @return Field
     */
    public function getCurrentField()
    {
        return $this->getCurrentContext()->getField();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setFieldOption($name, $value)
    {
        $this->getCurrentContext()->setOption($name, $value);
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->getCurrentContext()->getOptions();
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

    /**
     * @return FieldContextEntry
     */
    protected function getCurrentContext()
    {
        return end($this->contextEntries);
    }
}
