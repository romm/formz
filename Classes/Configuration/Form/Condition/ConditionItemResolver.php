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

namespace Romm\Formz\Configuration\Form\Condition;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\Formz\Condition\ConditionFactory;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use TYPO3\CMS\Extbase\Error\Error;

abstract class ConditionItemResolver extends AbstractFormzConfiguration implements MixedTypesInterface
{

    /**
     * Because this class implements `MixedTypeInterface`, this function needs
     * to be implemented.
     *
     * It will allow to fill properly the properties of type
     * `AbstractConditionItem` by returning the real type of the condition.
     *
     * The property `type` must be present in the resolver data, and contain one
     * of the values of `AbstractConditionItem::$types`.
     *
     * @param MixedTypesResolver $resolver
     */
    final public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $conditionFactory = ConditionFactory::get();
        $data = $resolver->getData();
        $conditionName = (true === is_array($data) && true === isset($data['type']))
            ? $data['type']
            : null;

        if (false === $conditionFactory->hasCondition($conditionName)) {
            $error = new Error(
                'No condition was found (type: "' . $data['type'] . '").',
                1471809847
            );

            $resolver->addError($error);
        } else {
            $resolver->setObjectType($conditionFactory->getCondition($conditionName));
        }
    }
}
