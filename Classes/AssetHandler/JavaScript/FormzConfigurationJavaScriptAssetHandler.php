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

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Service\ArrayService;
use Romm\Formz\Service\CacheService;

/**
 * This asset handler generates the JavaScript code which will inject the FormZ
 * TypoScript configuration.
 */
class FormzConfigurationJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * @return string
     */
    public function getJavaScriptFileName()
    {
        $hash = sha1($this->getFormObject()
            ->getConfiguration()
            ->getRootConfiguration()
            ->getHash());

        return CacheService::GENERATED_FILES_PATH . 'fz-config-' . $hash . '.js';
    }

    /**
     * @return string
     */
    public function getJavaScriptCode()
    {
        $jsonFormzConfiguration = $this->handleFormzConfiguration($this->getFormzConfiguration());

        return <<<JS
(function() {
    Fz.setConfiguration($jsonFormzConfiguration);
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
     * Returns a JSON array containing the FormZ configuration.
     *
     * @return string
     */
    protected function getFormzConfiguration()
    {
        $rootConfigurationArray = $this->getFormObject()
            ->getConfiguration()
            ->getRootConfiguration()
            ->toArray();

        $cleanFormzConfigurationArray = [
            'view' => $rootConfigurationArray['view']
        ];

        return ArrayService::get()->arrayToJavaScriptJson($cleanFormzConfigurationArray);
    }
}
