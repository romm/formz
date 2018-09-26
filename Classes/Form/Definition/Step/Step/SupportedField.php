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

namespace Romm\Formz\Form\Definition\Step\Step;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\FormDefinition;

class SupportedField extends AbstractFormDefinitionComponent
{
    use ParentsTrait;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $fieldName;

    /**
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        /** @var FormDefinition $form */
        $form = $this->getFirstParent(FormDefinition::class);

        return $form->getField($this->fieldName);
    }
}
