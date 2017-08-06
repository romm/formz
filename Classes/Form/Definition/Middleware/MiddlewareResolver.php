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

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\Formz\Validation\Validator\Internal\MiddlewareIsValidValidator;
use TYPO3\CMS\Extbase\Error\Error;

class MiddlewareResolver implements MixedTypesInterface
{
    /**
     * This method will fetch and validate the class name of the middleware
     * property. If any error is found, it is added to the result instance of
     * the resolver.
     *
     * @param MixedTypesResolver $resolver
     */
    final public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();

        if (isset($data['className'])) {
            $middlewareClassName = $data['className'];
            $validator = new MiddlewareIsValidValidator;
            $result = $validator->validate($middlewareClassName);

            if ($result->hasErrors()) {
                $resolver->getResult()->merge($result);
            } else {
                $resolver->setObjectType($middlewareClassName);
            }
        } else {
            $error = new Error('Property "className" must be filled with a valid middleware class name.', 1490798654);
            $resolver->getResult()->addError($error);
        }
    }
}
