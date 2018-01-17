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

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Exceptions\SilentException;
use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Step\Step\Substep\Substeps;

class Step extends AbstractFormDefinitionComponent implements DataPreProcessorInterface
{
    use ParentsTrait;
    use StoreArrayIndexTrait;

    /**
     * @var int
     * @validate IntegerValidator
     * @validate Romm.Formz:PageExists
     */
    protected $pageUid;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $action;

    /**
     * @var string[]
     */
    protected $authorizedActions = [];

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\SupportedField[]
     * @validate NotEmpty
     */
    protected $supportedFields;

    /**
     * @var \Romm\Formz\Form\Definition\Step\Step\Substep\Substeps
     */
    protected $substeps;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getArrayIndex();
    }

    /**
     * @return int
     */
    public function getPageUid()
    {
        return $this->pageUid;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getAuthorizedActions()
    {
        return array_merge([$this->action], $this->authorizedActions);
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
     * @return bool
     */
    public function hasSubsteps()
    {
        return $this->substeps instanceof Substeps;
    }

    /**
     * Alias for Fluid usage.
     *
     * @return bool
     */
    public function getHasSubsteps()
    {
        return $this->hasSubsteps();
    }

    /**
     * @return Substeps
     */
    public function getSubsteps()
    {
        if (false === $this->hasSubsteps()) {
            throw new SilentException('todo'); // @todo
        }

        return $this->substeps;
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
