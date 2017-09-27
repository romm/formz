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

namespace Romm\Formz\Form\Definition\Middleware;

use Romm\Formz\Form\Definition\AbstractFormDefinitionComponent;

class MiddlewareScopes extends AbstractFormDefinitionComponent
{
    /**
     * @todo desc
     *
     * List of interface names that do implement:
     *
     * @see \Romm\Formz\Middleware\Scope\ScopeInterface
     *
     * @var array
     * @validate NotEmpty
     * @todo validation type
     */
    protected $whiteList = [];

    /**
     * @todo desc
     *
     * List of interface names that do implement:
     *
     * @see \Romm\Formz\Middleware\Scope\ScopeInterface
     *
     * @var array
     * @todo validation type
     */
    protected $blackList = [];

    /**
     * @param array $whiteList
     * @param array $blackList
     */
    public function __construct(array $whiteList = [], array $blackList = [])
    {
        $this->whiteList = $whiteList;
        $this->blackList = $blackList;
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function isWhiteListed($scope)
    {
        return in_array($scope, $this->whiteList);
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function isBlackListed($scope)
    {
        return in_array($scope, $this->blackList);
    }
}
