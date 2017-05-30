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

namespace Romm\Formz\Form\Definition\Step\Step\Substep;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Step\Step\SupportedField;

class Substep extends AbstractFormDefinitionComponent implements DataPreProcessorInterface
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $identifier;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\SupportedField[]
     * @validate NotEmpty
     */
    protected $supportedFields;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return SupportedField[]
     */
    public function getSupportedFields()
    {
        return $this->supportedFields;
    }

    /**
     * @param Field $field
     * @return bool
     */
    public function supportsField(Field $field)
    {
        foreach ($this->supportedFields as $supportedField) {
            if ($supportedField->getField() === $field) {
                return true;
            }
        }

        return false;
    }

    /**
     * This function will parse the configuration for `supportedFields`: instead
     * of being forced to fill the `fieldName` option for each entry, the field
     * key of the entry can be the actual name of the field, and the value of
     * the entry can be anything but an array.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        $supportedFields = (isset($data['supportedFields']))
            ? $data['supportedFields']
            : [];

        foreach ($supportedFields as $key => $supportedField) {
            $supportedField = is_array($supportedField)
                ? $supportedField
                : [];
            $supportedField['fieldName'] = $key;

            $supportedFields[$key] = $supportedField;
        }

        $data['supportedFields'] = $supportedFields;

        $processor->setData($data);
    }
}
