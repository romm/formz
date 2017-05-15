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

namespace Romm\Formz\Configuration\View\Layouts;

use Romm\Formz\Configuration\AbstractConfiguration;

class Layout extends AbstractConfiguration
{
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $layout;

    /**
     * @var string
     */
    protected $templateFile;

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $templateFile = $this->templateFile;

        if (null === $templateFile
            && $this->hasParent(LayoutGroup::class)
        ) {
            $templateFile = $this->getFirstParent(LayoutGroup::class)->getTemplateFile();
        }

        return $this->getAbsolutePath($templateFile);
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        $this->checkConfigurationFreezeState();

        $this->templateFile = $templateFile;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->checkConfigurationFreezeState();

        $this->layout = $layout;
    }
}
