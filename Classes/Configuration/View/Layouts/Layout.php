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

use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use Romm\Formz\Configuration\AbstractFormzConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Layout extends AbstractFormzConfiguration
{
    use StoreArrayIndexTrait;
    use ParentsTrait;

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
        if (null === $this->templateFile) {
            $this->templateFile = $this->withFirstParent(
                LayoutGroup::class,
                function (LayoutGroup $parent) {
                    return $parent->getTemplateFile();
                }
            );
        }

        return GeneralUtility::getFileAbsFileName($this->templateFile);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
