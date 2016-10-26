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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\Core\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the JavaScript code which will initialize the
 * form and its whole configuration.
 */
class FormInitializationJavaScriptAssetHandler extends AbstractJavaScriptAssetHandler
{

    /**
     * Generates and returns JavaScript code to instantiate a new form.
     *
     * @return string
     */
    public function getFormInitializationJavaScriptCode()
    {
        $formConfigurationArray = $this->getFormObject()->getConfiguration()->toArray();
        $this->removeFieldsValidationConfiguration($formConfigurationArray)
            ->addClassNameProperty($formConfigurationArray);

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());
        $formConfigurationJson = Core::get()->arrayToJavaScriptJson($formConfigurationArray);

        $javaScriptCode = <<<JS
(function() {
    Formz.Form.register($formName, $formConfigurationJson);
})();
JS;

        return $javaScriptCode;
    }

    /**
     * To lower the length of the JavaScript code, we remove useless fields
     * validation configuration.
     *
     * @param array $formConfiguration
     * @return $this
     */
    protected function removeFieldsValidationConfiguration(array &$formConfiguration)
    {
        foreach ($formConfiguration['fields'] as $fieldName => $fieldConfiguration) {
            if (true === isset($fieldConfiguration['validation'])) {
                unset($fieldConfiguration['validation']);
                unset($fieldConfiguration['activation']);
                $formConfiguration['fields'][$fieldName] = $fieldConfiguration;
            }
        }

        return $this;
    }

    /**
     * Adds the "model" property to the form configuration, which can then be
     * used by JavaScript.
     *
     * @param array $formConfiguration
     */
    protected function addClassNameProperty(array &$formConfiguration)
    {
        $formConfiguration['className'] = $this->getFormObject()->getClassName();
    }
}
