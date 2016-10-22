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
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObject;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration extends AbstractFormzConfiguration implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;
    use ArrayConversionTrait;

    const HASH_FULL = 'full';
    const HASH_CONFIGURATION = 'configuration';

    /**
     * @var \Romm\Formz\Configuration\Settings\Settings
     */
    protected $settings;

    /**
     * @var \ArrayObject<Romm\Formz\Configuration\Form\Form>
     */
    protected $forms = [];

    /**
     * @var \Romm\Formz\Configuration\View\View
     */
    protected $view;

    /**
     * @var array
     */
    protected $hash = [];

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
     * @throws \Exception
     */
    public static function getConfigurationObjectServices()
    {
        $backendCache = Core::get()->getTypoScriptUtility()
            ->getExtensionConfigurationFromPath('settings.defaultBackendCache');

        if (false === class_exists($backendCache)
            && false === in_array(AbstractBackend::class, class_parents($backendCache))
        ) {
            throw new \Exception(
                'The cache class name given in configuration "config.tx_formz.settings.defaultBackendCache" must inherit "' . AbstractBackend::class . '" (current value: "' . (string)$backendCache . '")',
                1459251263
            );
        }

        $serviceFactory = ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_CACHE)
            ->with(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_BACKEND, $backendCache)
            ->attach(ServiceInterface::SERVICE_PARENTS)
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);

        return $serviceFactory;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Adds a form to the forms list of this Formz configuration. Note that this
     * function will also handle the parent service from the
     * `configuration_object` extension.
     *
     * @param FormObject $form
     */
    public function addForm(FormObject $form)
    {
        /** @var Form $configuration */
        $configuration = $form->getConfigurationObject()->getObject(true);

        $configuration->setParents([$this]);
        $this->forms[$form->getClassName()][$form->getName()] = $configuration;
    }

    /**
     * @return Form[]
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param string $className
     * @param string $name
     * @return bool
     */
    public function hasForm($className, $name)
    {
        return (true === isset($this->forms[$className][$name]));
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
     * Calculates several hashes for the class and its sub-properties. Can be
     * used as a unique identifier in further scripts.
     */
    public function calculateHashes()
    {
        $fullArray = $this->toArray();
        $configurationArray = [
            'view' => $fullArray['view']
        ];

        $this->hash = [
            self::HASH_FULL          => sha1(serialize($fullArray)),
            self::HASH_CONFIGURATION => sha1(serialize($configurationArray))
        ];
    }

    /**
     * @param string $identifier One of the `HASH_*` constants.
     * @return string
     */
    public function getHash($identifier = self::HASH_FULL)
    {
        return (true === isset($this->hash[$identifier]))
            ? $this->hash[$identifier]
            : null;
    }
}
