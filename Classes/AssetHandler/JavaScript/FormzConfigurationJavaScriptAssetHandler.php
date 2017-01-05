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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\Core\Core;

/**
 * This asset handler generates the JavaScript code which will inject the Formz
 * TypoScript configuration.
 */
class FormzConfigurationJavaScriptAssetHandler extends AbstractJavaScriptAssetHandler
{

    /**
     * @return string
     */
    public function getJavaScriptFileName()
    {
        $hash = $this->getFormObject()
            ->getConfiguration()
            ->getFormzConfiguration()
            ->getHash();

        return Core::GENERATED_FILES_PATH . 'formz-config-' . $hash . '.js';
    }

    /**
     * @return string
     */
    public function getJavaScriptCode()
    {
        $jsonFormzConfiguration = $this->handleFormzConfiguration($this->getFormzConfiguration());

        return <<<JS
(function() {
    Formz.setConfiguration($jsonFormzConfiguration);
})();
JS;
    }

    /**
     * This function is here to help unit tests mocking.
     *
     * @param string $formzConfiguration
     * @return string
     */
    protected function handleFormzConfiguration($formzConfiguration)
    {
        return $formzConfiguration;
    }

    /**
     * Returns a JSON array containing the formz configuration.
     *
     * @return string
     */
    protected function getFormzConfiguration()
    {
        $formzConfigurationArray = $this->getFormObject()
            ->getConfiguration()
            ->getFormzConfiguration()
            ->toArray();

        $cleanFormzConfigurationArray = [
            'view' => $formzConfigurationArray['view']
        ];

        return Core::get()->arrayToJavaScriptJson($cleanFormzConfigurationArray);
    }
}
