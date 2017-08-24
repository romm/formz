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

namespace Romm\Formz\Middleware\Request;

use Romm\Formz\Form\Definition\Step\Step\Step;
use Romm\Formz\Middleware\Request\Exception\RedirectException;

class Redirect extends Dispatcher
{
    /**
     * @var int
     */
    protected $pageUid;

    /**
     * @var int
     */
    protected $delay = 0;

    /**
     * @var int
     */
    protected $status = 303;

    /**
     * @var Step
     */
    protected $step;

    /**
     * @throws RedirectException
     */
    public function dispatch()
    {
        if ($this->step) {
            $this->pageUid = $this->step->getPageUid();
            $this->controller = $this->step->getController();
            $this->action = $this->step->getAction();
            $this->extension = $this->step->getExtension();
            $this->arguments['fz-hash'] = [$this->formObject->getName() => $this->formObject->getFormHash()];
        }

        throw new RedirectException(
            $this->action,
            $this->controller,
            $this->extension,
            $this->arguments,
            $this->pageUid,
            $this->delay,
            $this->status
        );
    }

    /**
     * @param Step $step
     * @return $this
     */
    public function toStep(Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @param int $pageUid
     * @return $this
     */
    public function toPage($pageUid)
    {
        $this->pageUid = $pageUid;

        return $this;
    }

    /**
     * @param int $delay
     * @return $this
     */
    public function withDelay($delay)
    {
        $this->delay = (int)$delay;

        return $this;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function withStatus($status)
    {
        $this->status = (int)$status;

        return $this;
    }
}
