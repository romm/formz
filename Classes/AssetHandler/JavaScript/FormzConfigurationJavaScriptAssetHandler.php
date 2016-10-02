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

use Romm\Formz\Configuration\Configuration;
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
        $hash = $this->getFormConfiguration()
            ->getFormzConfiguration()
            ->getHash(Configuration::HASH_CONFIGURATION);

        return Core::GENERATED_FILES_PATH . 'formz-config-' . $hash . '.js';
    }

    /**
     * @return string
     */
    public function getJavaScriptCode()
    {
        $formzConfigurationArray = $this->getFormConfiguration()
            ->getFormzConfiguration()
            ->toArray();

        $cleanFormzConfigurationArray = [
            'view' => $formzConfigurationArray['view']
        ];
        $jsonConfiguration = Core::arrayToJavaScriptJson($cleanFormzConfigurationArray);

        return <<<JS
(function() {
    Formz.setConfiguration($jsonConfiguration);
})();
JS;
    }
}
