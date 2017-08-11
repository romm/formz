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

namespace Romm\Formz\Controller;

use Romm\Formz\Controller\Processor\ControllerProcessor;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class FormActionController extends ActionController
{
    /**
     * In case an exception (any exception type) is thrown during the
     * middlewares execution, it can be automatically caught by FormZ, and the
     * request will be forwarded to an action of the controller.
     *
     * Just fill the property below with the name of an existing action of the
     * controller. The method will have a single parameter which is the
     * exception.
     *
     * You can use it for instance to log your exception in some log service; to
     * render a view that contains a message explaining to the user that
     * something went wrong.
     *
     * @var string
     */
    protected $actionForException;

    /**
     * IMPORTANT: if you need to override this method in your own controller, do
     * not forget to call `parent::initializeAction()`!
     */
    public function initializeAction()
    {
        $processor = ControllerProcessor::prepare($this->request, $this->arguments, $this->settings);

        if (null !== $this->actionForException) {
            $vendorName = $this->request->getControllerVendorName();
            $extensionName = $this->request->getControllerExtensionName();
            $controllerName = $this->request->getControllerName();


            $processor->setExceptionCallback(function ($exception) use ($vendorName, $controllerName, $extensionName) {
                $this->request->setControllerVendorName($vendorName);
                $this->forward($this->actionForException, $controllerName, $extensionName, ['exception' => $exception]);
            });
        }

        $processor->dispatch();
    }
}
