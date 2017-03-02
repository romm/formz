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

namespace Romm\Formz\Configuration\Form\Settings;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Service\ContextService;

class FormSettings extends AbstractFormzConfiguration
{
    use ParentsTrait;

    /**
     * @var string
     */
    protected $defaultClass;

    /**
     * @var string
     */
    protected $defaultErrorMessage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->defaultErrorMessage = 'default_error_message';
    }

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
        return ContextService::get()->translate($this->getSettingsProperty('defaultErrorMessage'));
    }

    /**
     * This function will do the following: first, it will check if the wanted
     * property is set in this class instance (not null), then it returns it. If
     * the value is null, it will fetch the global Formz configuration settings,
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
            if ($this->hasParent(Form::class)) {
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
