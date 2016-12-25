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

namespace Romm\Formz\ViewHelpers;

use Romm\Formz\ViewHelpers\Service\FormzViewHelperService;

abstract class AbstractViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var FormzViewHelperService
     */
    protected $service;

    /**
     * Initializes any ViewHelper correctly.
     */
    public function initialize()
    {
        $this->service = FormzViewHelperService::get();
    }

    /**
     * Checks that the current `FormViewHelper` exists. If not, an exception is
     * thrown.
     *
     * @throws \Exception
     */
    protected function checkIsInsideFormViewHelper()
    {
        $flag = $this->service->formContextExists();

        if (false === $flag) {
            throw new \Exception(
                'The view helper "' . get_called_class() . '" must be used inside the view helper "' . FormViewHelper::class . '".',
                1465243085
            );
        }
    }

    /**
     * Checks that the `FieldViewHelper` has been called. If not, an exception
     * is thrown.
     *
     * @throws \Exception
     */
    protected function checkIsInsideFieldViewHelper()
    {
        $flag = $this->service->fieldContextExists($this->renderingContext);

        if (false === $flag) {
            throw new \Exception(
                'The view helper "' . get_called_class() . '" must be used inside the view helper "' . FieldViewHelper::class . '".',
                1465243085
            );
        }
    }
}
