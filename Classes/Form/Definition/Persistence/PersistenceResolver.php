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

namespace Romm\Formz\Form\Definition\Persistence;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\Formz\Validation\Validator\Internal\PersistenceIsValidValidator;
use TYPO3\CMS\Extbase\Error\Error;

class PersistenceResolver implements MixedTypesInterface
{
    /**
     * This method will fetch and validate the class name of the persistence
     * property. If any error is found, it is added to the result instance of
     * the resolver.
     *
     * @param MixedTypesResolver $resolver
     */
    final public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();

        if (isset($data['className'])) {
            $className = $data['className'];
            $validator = new PersistenceIsValidValidator;
            $result = $validator->validate($className);

            if ($result->hasErrors()) {
                $resolver->getResult()->merge($result);
            } else {
                $resolver->setObjectType($className);
            }
        } else {
            $error = new Error('Property "className" must be filled with a valid persistence class name.', 1491223916);
            $resolver->getResult()->addError($error);
        }
    }
}
