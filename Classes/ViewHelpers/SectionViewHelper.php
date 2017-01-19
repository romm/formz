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

namespace Romm\Formz\ViewHelpers;

use TYPO3\CMS\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * This view helper registers a section which will not be rendered directly, but
 * with the usage of the `RenderSection` view helper.
 *
 * It is used to manage dynamic parts of the layouts used with the `field` view
 * helper: every layout can call as many sections as it needs, and this sections
 * must then be registered using this view helper.
 */
class SectionViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Contains the closures which will render the registered sections. The keys
     * of this array are the names of the sections.
     *
     * @var callable[]
     */
    private static $sections = [];

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the section.', true);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->service->checkIsInsideFieldViewHelper();

        self::addSectionClosure($this->arguments['name'], $this->buildRenderChildrenClosure());
    }

    /**
     * In the created PHP code, we add a call to the function which will
     * register the closure to render this section.
     *
     * @inheritdoc
     */
    public function compile($argumentsVariableName, $renderChildrenClosureVariableName, &$initializationPhpCode, AbstractNode $syntaxTreeNode, TemplateCompiler $templateCompiler)
    {
        $initializationPhpCode .= self::class . '::addSectionClosure(' . $argumentsVariableName . "['name'], " . $renderChildrenClosureVariableName . ');' . LF;

        return '""';
    }

    /**
     * Adds a closure - which will render the section with the given name - to
     * the private storage in this class.
     *
     * @param string   $name
     * @param callable $closure
     */
    public static function addSectionClosure($name, $closure)
    {
        self::$sections[$name] = $closure;
    }

    /**
     * Returns the closure which will render the section with the given name. If
     * nothing is found, `null` is returned.
     *
     * @param string $name
     * @return callable|null
     */
    public static function getSectionClosure($name)
    {
        return (true === isset(self::$sections[$name]))
            ? self::$sections[$name]
            : null;
    }

    /**
     * Resets the list of closures.
     */
    public static function resetSectionClosures()
    {
        self::$sections = [];
    }
}
