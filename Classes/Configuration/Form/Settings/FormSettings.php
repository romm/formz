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

namespace Romm\Formz\Configuration\Form\Settings;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Service\ContextService;

class FormSettings extends AbstractFormzConfiguration
{
    use ParentsTrait;

    const DEFAULT_ERROR_MESSAGE_KEY = 'default_error_message';

    /**
     * @var string
     */
    protected $defaultClass;

    /**
     * @var string
     */
    protected $defaultErrorMessage;

    /**
     * @return string
     */
    public function getDefaultClass()
    {
        return $this->getSettingsProperty('defaultClass');
    }

    /**
     * @param string $defaultClass
     */
    public function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
    }

    /**
     * @return string
     */
    public function getDefaultErrorMessage()
    {
        $message = $this->getSettingsProperty('defaultErrorMessage') ?: self::DEFAULT_ERROR_MESSAGE_KEY;

        return ContextService::get()->translate($message);
    }

    /**
     * @param string $defaultErrorMessage
     */
    public function setDefaultErrorMessage($defaultErrorMessage)
    {
        $this->defaultErrorMessage = $defaultErrorMessage;
    }

    /**
     * This function will do the following: first, it will check if the wanted
     * property is set in this class instance (not null), then it returns it. If
     * the value is null, it will fetch the global FormZ configuration settings,
     * and return the default value for the asked property.
     *
     * Example:
     *  config.tx_formz.forms.My\Custom\Form.settings.defaultClass is null, then
     *  config.tx_formz.settings.defaultFormSettings.defaultClass is returned
     *
     * @param string $propertyName Name of the wanted class property.
     * @return mixed|null
     */
    private function getSettingsProperty($propertyName)
    {
        $result = $this->$propertyName;

        if (empty($result)) {
            if ($this->hasParent(Form::class)
                && $this->hasParent(Configuration::class)
            ) {
                $result = $this->withFirstParent(
                    Configuration::class,
                    function (Configuration $configuration) use ($propertyName) {
                        $getter = 'get' . ucfirst($propertyName);

                        return $configuration->getSettings()->getDefaultFormSettings()->$getter();
                    }
                );
            }
        }

        return $result;
    }
}
