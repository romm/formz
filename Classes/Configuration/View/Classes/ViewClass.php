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

namespace Romm\Formz\Configuration\View\Classes;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\Formz\Configuration\AbstractFormzConfiguration;

class ViewClass extends AbstractFormzConfiguration implements DataPreProcessorInterface
{

    /**
     * @var array
     */
    protected $items = [];

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

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $itemName
     * @return bool
     */
    public function hasItem($itemName)
    {
        return true === isset($this->items[$itemName]);
    }

    /**
     * @param string $itemName
     * @return array|null
     */
    public function getItem($itemName)
    {
        return (true === isset($this->items[$itemName]))
            ? $this->items[$itemName]
            : null;
    }

    /**
     * @param string $itemName
     * @param string $value
     */
    public function addItem($itemName, $value)
    {
        $this->items[$itemName] = $value;
    }
}
