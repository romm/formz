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

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Condition\Parser\ConditionParserFactory;
use Romm\Formz\Condition\Parser\Node\ConditionNode;
use Romm\Formz\Condition\Parser\Node\NodeInterface;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Condition\Parser\Tree\EmptyConditionTree;
use Romm\Formz\Form\Definition\Condition\ActivationInterface;
use Romm\Formz\Form\Definition\Field\Field;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use Romm\Formz\Form\Definition\Step\Step\StepDefinition;
use Romm\Formz\Form\Definition\Step\Step\Substep\SubstepDefinition;
use Romm\Formz\Form\FormObject\FormObject;

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
    private $conditionTrees = [];

    /**
     * @var ConditionTree[]
     */
    private $stepTree = [];

    /**
     * @var ConditionTree[]
     */
    private $substepTree = [];

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
        $key = $field->getName();

        if (false === array_key_exists($key, $this->fieldsTrees)) {
            $this->fieldsTrees[$key] = $field->hasActivation()
                ? $this->getConditionTree($field->getActivation())
                : EmptyConditionTree::get();
        }

        if ($field->hasActivation()) {
            $this->fieldsTrees[$key]->injectDependencies($this, $field->getActivation());
        }

        return $this->fieldsTrees[$key];
    }

    /**
     * Returns the condition tree for a given validator instance, giving access
     * to CSS, JavaScript and PHP transpiled results.
     *
     * @param Validator $validator
     * @return ConditionTree
     */
    public function getActivationConditionTreeForValidator(Validator $validator)
    {
        $key = $validator->getParentField()->getName() . '->' . $validator->getName();

        if (false === array_key_exists($key, $this->conditionTrees)) {
            $this->conditionTrees[$key] = $validator->hasActivation()
                ? $this->getConditionTree($validator->getActivation())
                : EmptyConditionTree::get();
        }

        if ($validator->hasActivation()) {
            $this->conditionTrees[$key]->injectDependencies($this, $validator->getActivation());
        }

        return $this->conditionTrees[$key];
    }

    /**
     * @todo
     *
     * @param StepDefinition $stepDefinition
     * @return ConditionTree
     */
    public function getActivationConditionTreeForStep(StepDefinition $stepDefinition)
    {
        $key = 'step-' . $stepDefinition->hash();

        if (false === array_key_exists($key, $this->stepTree)) {
            $this->stepTree[$key] = $this->getConditionTree($stepDefinition->getActivation());
        }

        $this->stepTree[$key]->injectDependencies($this, $stepDefinition->getActivation());

        return $this->stepTree[$key];
    }

    /**
     * @todo
     *
     * @param SubstepDefinition $substep
     * @return ConditionTree
     */
    public function getActivationConditionTreeForSubstep(SubstepDefinition $substep)
    {
        $key = 'substep-' . $substep->hash();

        if (false === array_key_exists($key, $this->substepTree)) {
            $this->substepTree[$key] = $this->getConditionTree($substep->getActivation());
        }

        $this->substepTree[$key]->injectDependencies($this, $substep->getActivation());

        return $this->substepTree[$key];
    }

    /**
     * Function that will calculate all trees from fields and their validators.
     *
     * This is useful to be able to store this instance in cache.
     */
    public function calculateAllTrees()
    {
        $fields = $this->formObject->getDefinition()->getFields();

        foreach ($fields as $field) {
            $this->getActivationConditionTreeForField($field);

            foreach ($field->getValidators() as $validator) {
                $this->getActivationConditionTreeForValidator($validator);
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
            ->parse($activation);
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
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->formObject;
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
        return ['fieldsTrees', 'conditionTrees', 'javaScriptFiles'];
    }
}
