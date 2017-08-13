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

namespace Romm\Formz\Middleware;

use Romm\Formz\Core\Core;
use Romm\Formz\Exceptions\ClassNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentTypeException;
use Romm\Formz\Middleware\Option\AbstractOptionDefinition;
use Romm\Formz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class MiddlewareFactory implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @param ReflectionService $reflectionService
     */
    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @param string   $className
     * @param callable $optionsCallback
     * @return MiddlewareComponentInterface
     * @throws ClassNotFoundException
     * @throws InvalidArgumentTypeException
     */
    public function create($className, callable $optionsCallback = null)
    {
        if (false === class_exists($className)) {
            throw ClassNotFoundException::middlewareClassNameNotFound($className);
        }

        if (false === in_array(MiddlewareComponentInterface::class, class_implements($className))) {
            throw InvalidArgumentTypeException::middlewareWrongClassName($className);
        }

        $optionsType = $this->getOptionsType($className);
        $options = Core::instantiate($optionsType);

        if (is_callable($optionsCallback)) {
            call_user_func($optionsCallback, $options);
        }

        /** @var MiddlewareComponentInterface $middleware */
        $middleware = Core::instantiate($className, $options);

        return $middleware;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getOptionsType($className)
    {
        $property = $this->reflectionService->getClassSchema($className)->getProperty('options');
        $optionsType = $property['type'];

        if (false === class_exists($optionsType)) {
            throw new \Exception('todo'); // todo exception
        }

        if (false === in_array(AbstractOptionDefinition::class, class_parents($optionsType))) {
            throw new \Exception('todo'); // todo exception
        }

        return $optionsType;
    }
}
