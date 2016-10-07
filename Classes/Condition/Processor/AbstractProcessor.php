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

namespace Romm\Formz\Condition\Processor;

use Romm\Formz\Condition\ConditionParser;
use Romm\Formz\Configuration\Form\Condition\ActivationInterface;
use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;

/**
 * Abstract condition processor.
 */
abstract class AbstractProcessor implements ProcessorInterface
{

    /**
     * @var FormObject
     */
    protected $formObject;

    /**
     * @var array
     */
    protected $fieldsConditionTrees = [];

    /**
     * @var ConditionParser[]
     */
    protected $conditionParser = [];

    /**
     * Should this processor store its trees in cache? This is useful for
     * processors which generates code lines like CSS/JavaScript, but should not
     * be used for one-time-run processors like PHP.
     *
     * @var bool
     */
    protected static $storeInCache = true;

    /**
     * @inheritdoc
     */
    public function __construct(FormObject $formObject)
    {
        $this->formObject = $formObject;
        $formConfiguration = $this->formObject->getConfiguration();

        if (true === static::$storeInCache) {
            $cacheInstance = Core::get()->getCacheInstance();
            if ($cacheInstance) {
                $cacheIdentifier = 'processor-' . sha1(get_class($this) . $this->formObject->getClassName());

                if ($cacheInstance->has($cacheIdentifier)) {
                    $this->fieldsConditionTrees = $cacheInstance->get($cacheIdentifier);
                } else {
                    $this->fieldsConditionTrees = $this->createConditionTrees($formConfiguration);
                    $cacheInstance->set($cacheIdentifier, $this->fieldsConditionTrees);
                }
            } else {
                $this->fieldsConditionTrees = $this->createConditionTrees($formConfiguration);
            }
        }
    }

    /**
     * Creates the whole condition trees for all fields from a form
     * configuration, and all their validation rules too.
     *
     * @param Form $configuration
     * @return array
     */
    protected function createConditionTrees(Form $configuration)
    {
        $result = [];

        foreach ($configuration->getFields() as $field) {
            $trees = [
                'activationConditionTree'            => null,
                'validationActivationConditionTrees' => []
            ];

            if ($field->hasActivation()) {
                $trees['activationConditionTree'] = $this->generateFieldActivationConditionTree($field);
            }

            foreach ($field->getValidation() as $validationName => $validation) {
                $validationTree = ($validation->hasActivation())
                    ? $this->generateFieldValidationActivationConditionTree($field, $validation)
                    : null;

                $trees['validationActivationConditionTrees'][$validation->getValidationName()] = $validationTree;
            }

            $result[$field->getFieldName()] = $trees;
        }

        return $result;
    }

    /**
     * Returns the final result of a field activation for the processor, after
     * the nodes have all been processed.
     *
     * @inheritdoc
     */
    final public function getFieldActivationConditionTree(Field $field)
    {
        $result = null;

        if (true === static::$storeInCache) {
            if (true === isset($this->fieldsConditionTrees[$field->getFieldName()])) {
                $result = $this->fieldsConditionTrees[$field->getFieldName()]['activationConditionTree'];
            }
        } elseif ($field->hasActivation()) {
            $result = $this->generateFieldActivationConditionTree($field);
        }

        return $result;
    }

    /**
     * Internal function which will actually generate the whole tree result for
     * a field activation condition.
     *
     * @param Field $field
     * @return mixed
     */
    protected function generateFieldActivationConditionTree(Field $field)
    {
        $conditionParser = $this->processConditionParsing($field->getActivation());

        return $this->getCleanParserNodeData($conditionParser, $field);
    }

    /**
     * Returns the final result of a the given field validation rule activation
     * for the processor, after the nodes have all been processed.
     *
     * @inheritdoc
     */
    final public function getFieldValidationActivationConditionTree(Field $field, Validation $validation)
    {
        $result = null;

        if (true === static::$storeInCache
            && true === isset($this->fieldsConditionTrees[$field->getFieldName()])
            && true === isset($this->fieldsConditionTrees[$field->getFieldName()]['validationActivationConditionTrees'][$validation->getValidationName()])
        ) {
            $result = $this->fieldsConditionTrees[$field->getFieldName()]['validationActivationConditionTrees'][$validation->getValidationName()];
        } elseif ($validation->hasActivation()) {
            $result = $this->generateFieldValidationActivationConditionTree($field, $validation);
        }

        return $result;
    }

    /**
     * Internal function which will actually generate the whole tree result for
     * a field validation activation condition.
     *
     * @param Field      $field
     * @param Validation $validation
     * @return mixed
     */
    protected function generateFieldValidationActivationConditionTree(Field $field, Validation $validation)
    {
        $activation = $validation->getActivation();
        $conditionParser = $this->processConditionParsing($activation);

        return $this->getCleanParserNodeData($conditionParser, $field);
    }

    /**
     * Will process a given field by parsing its validation conditions, and
     * returning a result depending on the processor type.
     *
     * @param ActivationInterface $activation Name of the field.
     * @return ConditionParser
     * @throws \Exception
     */
    protected function processConditionParsing(ActivationInterface $activation)
    {
        $cacheIdentifier = $this->getConditionHash($activation);

        if (false === isset($this->conditionParser[$cacheIdentifier])) {
            if (null === $this->formObject) {
                throw new \Exception('You need to give a form object to the processor first.', 1457623098);
            }

            $cacheInstance = Core::get()->getCacheInstance();

            if ($cacheInstance) {
                if ($cacheInstance->has($cacheIdentifier)) {
                    $this->conditionParser[$cacheIdentifier] = $cacheInstance->get($cacheIdentifier);
                    $this->conditionParser[$cacheIdentifier]->injectObjects($this, $activation);
                } else {
                    $this->conditionParser[$cacheIdentifier] = ConditionParser::parse($activation, $this);
                    $cacheInstance->set($cacheIdentifier, $this->conditionParser[$cacheIdentifier]);
                }
            } else {
                $this->conditionParser[$cacheIdentifier] = ConditionParser::parse($activation, $this);
            }
        }

        return $this->conditionParser[$cacheIdentifier];
    }

    /**
     * Will check if the condition parser did run correctly, and return a
     * correct result if so. Returns null on any issue.
     *
     * @param ConditionParser $conditionParser The condition parser instance.
     * @param Field           $field           Field instance.
     * @return array|null
     */
    protected function getCleanParserNodeData(ConditionParser $conditionParser, Field $field)
    {
        $nodeData = null;
        if (false === $conditionParser->getResult()->hasErrors()) {
            $nodeData = $conditionParser->getTree()->getResult($field);
            if (false === is_array($nodeData)
                && false === $this instanceof PhpProcessor
            ) {
                $nodeData = [$nodeData];
            }
        }

        return $nodeData;
    }

    /**
     * @param ActivationInterface $activation
     * @return string
     */
    protected function getConditionHash(ActivationInterface $activation)
    {
        return 'condition-' .
        sha1(
            get_class($this) .
            $this->formObject->getClassName() .
            serialize(
                [
                    $activation->getCondition(),
                    $activation->getItems()
                ]
            )
        );
    }
}
