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

namespace Romm\Formz\Configuration\Form\Condition\Activation;


use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;

class ActivationResolver extends AbstractActivation implements MixedTypesInterface
{
    /**
     * Because this class implements `MixedTypeInterface`, this function needs
     * to be implemented.
     *
     * It will detect empty condition (not registered or empty expression), and
     * force the type to `EmptyActivation` instead of `Activation`.
     *
     * @param MixedTypesResolver $resolver
     */
    final public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();
        $objectType = (empty(trim($data['condition'])))
            ? EmptyActivation::class
            : Activation::class;

        $resolver->setObjectType($objectType);
    }
}
