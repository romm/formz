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

namespace Romm\Formz\ViewHelpers\Legacy;

use Romm\Formz\ViewHelpers\FormViewHelper;

/**
 * Legacy version of the `FormViewHelper` for TYPO3 6.2+ and 7.6+, used to
 * register arguments the "old" way: in the DocBlock of the `render()` function.
 */
class OldFormViewHelper extends FormViewHelper
{
    /**
     * Render the form.
     *
     * @param string $action                               Target action
     * @param array  $arguments                            Arguments
     * @param string $controller                           Target controller
     * @param string $extensionName                        Target Extension Name (without "tx_" prefix and no underscores). If NULL the current extension name is used
     * @param string $pluginName                           Target plugin. If empty, the current plugin name is used
     * @param int    $pageUid                              Target page uid
     * @param mixed  $object                               Object to use for the form. Use in conjunction with the "property" attribute on the sub tags
     * @param int    $pageType                             Target page type
     * @param bool   $noCache                              set this to disable caching for the target page. You should not need this.
     * @param bool   $noCacheHash                          set this to suppress the cHash query parameter created by TypoLink. You should not need this.
     * @param string $section                              The anchor to be added to the action URI (only active if $actionUri is not set)
     * @param string $format                               The requested format (e.g. ".html") of the target page (only active if $actionUri is not set)
     * @param array  $additionalParams                     additional action URI query parameters that won't be prefixed like $arguments (overrule $arguments) (only active if $actionUri is not set)
     * @param bool   $absolute                             If set, an absolute action URI is rendered (only active if $actionUri is not set)
     * @param bool   $addQueryString                       If set, the current query parameters will be kept in the action URI (only active if $actionUri is not set)
     * @param array  $argumentsToBeExcludedFromQueryString arguments to be removed from the action URI. Only active if $addQueryString = TRUE and $actionUri is not set
     * @param string $fieldNamePrefix                      Prefix that will be added to all field names within this form. If not set the prefix will be tx_yourExtension_plugin
     * @param string $actionUri                            can be used to overwrite the "action" attribute of the form tag
     * @param string $objectName                           name of the object that is bound to this form. If this argument is not specified, the name attribute of this form is used to determine the FormObjectName
     * @param string $hiddenFieldClassName
     * @param string $addQueryStringMethod
     * @return string rendered form
     */
    public function render($action = null, array $arguments = [], $controller = null, $extensionName = null, $pluginName = null, $pageUid = null, $object = null, $pageType = 0, $noCache = false, $noCacheHash = false, $section = '', $format = '', array $additionalParams = [], $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = [], $fieldNamePrefix = null, $actionUri = null, $objectName = null, $hiddenFieldClassName = null, $addQueryStringMethod = '')
    {
        return call_user_func_array([get_parent_class(), 'renderViewHelper'], func_get_args());
    }
}
