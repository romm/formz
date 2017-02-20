<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Condition\Parser\ConditionTree;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Configuration\Form\Condition\Activation\ActivationInterface;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Form\FormObject;

class ConditionProcessor
{
    /**
     * @var FormObject
     */
    private $formObject;

    /**
     * @var ConditionTree[]
     */
    private $fieldsTrees = [];

    /**
     * @var ConditionTree[]
     */
    private $validationsTrees = [];

    /**
     * @var array
     */
    private $javaScriptFiles = [];

    /**
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        $this->attachFormObject($formObject);
    }

    /**
     * Returns the condition tree for a given field instance, giving access to
     * CSS, JavaScript and PHP transpiled results.
     *
     * @param Field $field
     * @return ConditionTree
     */
    public function getActivationConditionTreeForField(Field $field)
    {
        $key = $field->getFieldName();

        if (false === array_key_exists($key, $this->fieldsTrees)) {
            $this->fieldsTrees[$key] = $this->getConditionTree($field->getActivation());
        }

        return $this->fieldsTrees[$key];
    }

    /**
     * Returns the condition tree for a given validation instance, giving access
     * to CSS, JavaScript and PHP transpiled results.
     *
     * @param Validation $validation
     * @return ConditionTree
     */
    public function getActivationConditionTreeForValidation(Validation $validation)
    {
        $key = $validation->getParentField()->getFieldName() . '->' . $validation->getValidationName();

        if (false === array_key_exists($key, $this->validationsTrees)) {
            $this->validationsTrees[$key] = $this->getConditionTree($validation->getActivation());
        }

        return $this->validationsTrees[$key];
    }

    /**
     * Function that will calculate all trees from fields and their validation
     * rules.
     *
     * This is useful to be able to store this instance in cache.
     */
    public function calculateAllTrees()
    {
        $fields = $this->formObject->getConfiguration()->getFields();

        foreach ($fields as $field) {
            $this->getActivationConditionTreeForField($field);

            foreach ($field->getValidation() as $validation) {
                $this->getActivationConditionTreeForValidation($validation);
            }
        }
    }

    /**
     * @param ActivationInterface $activation
     * @return ConditionTree
     */
    protected function getConditionTree(ActivationInterface $activation)
    {
        $tree = $this->getNewConditionTreeFromActivation($activation);
        $tree->alongNodes(function (NodeInterface $node) {
            $this->attachNodeJavaScriptFiles($node);
        });

        return $tree;
    }

    /**
     * @param ActivationInterface $activation
     * @return ConditionTree
     */
    protected function getNewConditionTreeFromActivation(ActivationInterface $activation)
    {
        return ConditionParserFactory::get()
            ->parse($activation)
            ->attachConditionProcessor($this);
    }

    /**
     * @param NodeInterface $node
     */
    protected function attachNodeJavaScriptFiles(NodeInterface $node)
    {
        if ($node instanceof ConditionNode) {
            $files = $node->getCondition()->getJavaScriptFiles();

            foreach ($files as $file) {
                if (false === in_array($file, $this->javaScriptFiles)) {
                    $this->javaScriptFiles[] = $file;
                }
            }
        }
    }

    /**
     * @param FormObject $formObject
     */
    public function attachFormObject(FormObject $formObject)
    {
        $this->formObject = $formObject;
    }

    /**
     * @return array
     */
    public function getJavaScriptFiles()
    {
        return $this->javaScriptFiles;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['fieldsTrees', 'validationsTrees', 'javaScriptFiles'];
    }
}
