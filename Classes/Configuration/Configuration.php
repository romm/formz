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

namespace Romm\Formz\Configuration;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\Formz\Configuration\Form\Form;
use Romm\Formz\Configuration\Settings\Settings;
use Romm\Formz\Configuration\View\View;
use Romm\Formz\Exceptions\DuplicateEntryException;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration extends AbstractFormzConfiguration implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;
    use ArrayConversionTrait;

    /**
     * @var \Romm\Formz\Configuration\Settings\Settings
     */
    protected $settings;

    /**
     * @var FormObject[]
     */
    protected $forms = [];

    /**
     * @var \Romm\Formz\Configuration\View\View
     */
    protected $view;

    /**
     * @var string
     */
    protected $hash;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings = GeneralUtility::makeInstance(Settings::class);
        $this->view = GeneralUtility::makeInstance(View::class);
    }

    /**
     * Will initialize correctly the configuration object settings.
     *
     * @return ServiceFactory
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_CACHE)
            ->with(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_BACKEND, \Romm\Formz\Service\CacheService::get()->getBackendCache())
            ->attach(ServiceInterface::SERVICE_PARENTS)
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Adds a form to the forms list of this FormZ configuration. Note that this
     * function will also handle the parent service from the
     * `configuration_object` extension.
     *
     * @param FormObject $form
     * @throws DuplicateEntryException
     */
    public function addForm(FormObject $form)
    {
        if (true === $this->hasForm($form->getClassName(), $form->getName())) {
            throw DuplicateEntryException::formWasAlreadyRegistered($form);
        }

        $form->getConfiguration()->setParents([$this]);

        $this->forms[$form->getClassName()][$form->getName()] = $form;
    }

    /**
     * @param string $className
     * @param string $name
     * @return bool
     */
    public function hasForm($className, $name)
    {
        return true === isset($this->forms[$className][$name]);
    }

    /**
     * @param string $className
     * @param string $name
     * @return null|Form
     */
    public function getForm($className, $name)
    {
        return ($this->hasForm($className, $name))
            ? $this->forms[$className][$name]
            : null;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Calculates a hash for this configuration, which can be used as a unique
     * identifier. It should be called once, before the configuration is put in
     * cache, so it is not needed to call it again after being fetched from
     * cache.
     */
    public function calculateHash()
    {
        $fullArray = $this->toArray();
        $configurationArray = [
            'view'     => $fullArray['view'],
            'settings' => $fullArray['settings']
        ];

        $this->hash = sha1(serialize($configurationArray));
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
