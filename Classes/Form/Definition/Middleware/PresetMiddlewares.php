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

namespace Romm\Formz\Form\Definition\Middleware;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use Romm\Formz\Middleware\Item\Field\Focus\FieldFocusMiddleware;
use Romm\Formz\Middleware\Item\FormInjection\FormInjectionMiddleware;
use Romm\Formz\Middleware\Item\FormValidation\FormValidationMiddleware;
use Romm\Formz\Middleware\Item\Persistence\PersistenceFetchingMiddleware;
use Romm\Formz\Middleware\Item\Step\StepDispatchingMiddleware;
use Romm\Formz\Middleware\Item\Step\StepFetchingMiddleware;
use Romm\Formz\Middleware\MiddlewareInterface;

class PresetMiddlewares implements DataPreProcessorInterface
{
    use MagicMethodsTrait;

    /**
     * @var \Romm\Formz\Middleware\Item\FormInjection\FormInjectionMiddleware
     */
    protected $formInjectionMiddleware;

    /**
     * @var \Romm\Formz\Middleware\Item\FormValidation\FormValidationMiddleware
     */
    protected $formValidationMiddleware;

    /**
     * @var \Romm\Formz\Middleware\Item\Persistence\PersistenceFetchingMiddleware
     */
    protected $persistenceFetchingMiddleware;

    /**
     * @var \Romm\Formz\Middleware\Item\Step\StepFetchingMiddleware
     */
    protected $stepFetchingMiddleware;

    /**
     * @var \Romm\Formz\Middleware\Item\Step\StepDispatchingMiddleware
     */
    protected $stepDispatchingMiddleware;

    /**
     * @var \Romm\Formz\Middleware\Item\Field\Focus\FieldFocusMiddleware
     */
    protected $fieldFocusMiddleware;

    /**
     * Returns the full list of preset middlewares.
     *
     * @return MiddlewareInterface[]
     */
    public function getList()
    {
        return get_object_vars($this);
    }

    /**
     * Fills middlewares by default (if they are not filled in configuration).
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        foreach (array_keys(get_class_vars(self::class)) as $middleware) {
            if (false === isset($data[$middleware])) {
                $data[$middleware] = [];
            }
        }

        $processor->setData($data);
    }

    /**
     * @return FormInjectionMiddleware
     */
    public function getFormInjectionMiddleware()
    {
        return $this->formInjectionMiddleware;
    }

    /**
     * @return FormValidationMiddleware
     */
    public function getFormValidationMiddleware()
    {
        return $this->formValidationMiddleware;
    }

    /**
     * @return PersistenceFetchingMiddleware
     */
    public function getPersistenceFetchingMiddleware()
    {
        return $this->persistenceFetchingMiddleware;
    }

    /**
     * @return StepFetchingMiddleware
     */
    public function getStepFetchingMiddleware()
    {
        return $this->stepFetchingMiddleware;
    }

    /**
     * @return StepDispatchingMiddleware
     */
    public function getStepDispatchingMiddleware()
    {
        return $this->stepDispatchingMiddleware;
    }

    /**
     * @return FieldFocusMiddleware
     */
    public function getFieldFocusMiddleware()
    {
        return $this->fieldFocusMiddleware;
    }
}
