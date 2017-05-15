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

namespace Romm\Formz\Configuration\View\Classes;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Configuration\AbstractConfiguration;
use Romm\Formz\Exceptions\EntryNotFoundException;

class ViewClass extends AbstractConfiguration implements DataPreProcessorInterface
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasItem($name)
    {
        return true === isset($this->items[$name]);
    }

    /**
     * @param string $name
     * @return array
     * @throws EntryNotFoundException
     */
    public function getItem($name)
    {
        if (false === $this->hasItem($name)) {
            throw EntryNotFoundException::viewClassNotFound($name);
        }

        return $this->items[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setItem($name, $value)
    {
        $this->checkConfigurationFreezeState();

        $this->items[$name] = $value;
    }

    /**
     * This function is called before the TypoScript data is injected in this
     * class. We use it to put all the array data in the key `items`, so it can
     * be properly injected in the `$items` property.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $finalData = [];
        $data = $processor->getData();

        foreach ($data as $key => $value) {
            $finalData[$key] = (string)$value;
        }

        $processor->setData(['items' => $finalData]);
    }
}
