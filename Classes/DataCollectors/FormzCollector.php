<?php
/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Romm\Formz\DataCollectors;

use Konafets\TYPO3DebugBar\DataCollectors\BaseCollector;
use Romm\Formz\Form\FormObject;
use Romm\Formz\Form\FormObjectFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class FormzCollector extends BaseCollector
{
    /**
     * @var FormObject[]
     */
    private $formzInstances = [];

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect()
    {
        $formzInstances = $this->getFormzInstances();

        $debug = '';

        foreach ($formzInstances as $instance) {
            $debug .= DebuggerUtility::var_dump($instance, $instance->getName(), 10, false, true, true);
            $forms[] = [
                'name' => $instance->getName(),
                'params' => [
                    'Class Name' => $instance->getClassName(),
                    'Fields' => $instance->getProperties(),
                    'Hash' => $instance->getHash()
                ]
            ];
        }

        $formsCount = \count($formzInstances);
        $output = [
            'formsCount' => $formsCount, // Number of formz instance in the page
            'debug' => $debug,
            'status' => '<b>' . $formsCount . '</b> Formz instance(s) in this page',
            'forms' => $forms,
            'console' => '' // div for output console javascript
        ];

        return $output;
    }

    /**
     * @return FormObject[]
     */
    public function getFormzInstances()
    {
        $formObjFactory = GeneralUtility::makeInstance(FormObjectFactory::class);

        foreach ($formObjFactory->getInstances() as $instance) {
            $this->formzInstances[$instance->getName()] = $instance;
        }

        return $this->formzInstances;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    public function getWidgets()
    {
        $name = $this->getName();

        return [
            (string) $name => [
                'icon' => $name,
                'widget' => 'PhpDebugBar.Widgets.FormzWidget',
                'map' => $name,
                'default' => '[]',
            ],
            "$name:badge" => [
                'map' => 'formz.formsCount',
                'default' => 0,
            ],
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    public function getName()
    {
        return 'formz';
    }
}
